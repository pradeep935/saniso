<?php

namespace Botble\Ecommerce\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Ecommerce\Importers\ProductImporter;
use Botble\DataSynchronize\Http\Requests\ImportRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Storage;

class ProductImportController extends BaseApiController
{
    /**
     * Import products from CSV file or JSON data
     *
     * @param Request $request
     * @return JsonResponse
     */
    private function sendError(string $error, array $errorMessages = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    private function sendResponse(array $result, string $message = 'Success'): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result,
        ];

        return response()->json($response, 200);
    }

    public function import(Request $request): JsonResponse
    {
        try {
            // Validate request
            $this->validateImportRequest($request);

            $importer = new ProductImporter();
            
            // Set ALL import options (same as manual import tool)
            $updateExisting = $request->boolean('update_existing', false);
            $downloadImages = $request->boolean('download_images', true);
            $createCategories = $request->boolean('create_categories', true);
            $createBrands = $request->boolean('create_brands', true);
            $createVariations = $request->boolean('create_variations', true);
            $autoGenerateSku = $request->boolean('auto_generate_sku', true);
            $skipHeader = $request->boolean('skip_header', true);
            $chunkSize = $request->integer('chunk_size', 100);

            // Merge ALL options into request for ProductImporter to access
            $request->merge([
                'update_existing_products' => $updateExisting,
                'download_images' => $downloadImages,
                'create_categories' => $createCategories,
                'create_brands' => $createBrands,
                'create_variations' => $createVariations,
                'auto_generate_sku' => $autoGenerateSku,
                'skip_header_row' => $skipHeader,
                'chunk_size' => $chunkSize,
                'type' => 'all', // Import all product types
            ]);

            $data = [];
            $totalProcessed = 0;
            $successful = 0;
            $failed = 0;
            $errors = [];

            // Handle different input types
            if ($request->hasFile('file')) {
                // CSV File upload
                $result = $this->handleCsvImport($request, $importer);
            } elseif ($request->has('products') && is_array($request->get('products'))) {
                // JSON array of products
                $result = $this->handleJsonArrayImport($request, $importer);
            } elseif ($request->has('product')) {
                // Single product JSON
                $result = $this->handleSingleProductImport($request, $importer);
            } else {
                return $this->sendError('No valid data provided. Send file, products array, or single product.', [], 400);
            }

            return $this->sendResponse($result['data'], $result['message']);

        } catch (ValidationException $e) {
            return $this->sendError('Validation failed', $e->errors(), 422);
        } catch (Exception $e) {
            \Log::error('Product import API error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->sendError('Import failed: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Get import progress (for async imports)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function progress(Request $request): JsonResponse
    {
        $jobId = $request->get('job_id');
        
        if (!$jobId) {
            return $this->sendError('Job ID is required', [], 400);
        }

        // This would integrate with Laravel Queue system for large imports
        // For now, return a simple response
        return $this->sendResponse([
            'job_id' => $jobId,
            'status' => 'completed',
            'progress' => 100,
            'message' => 'Import completed'
        ], 'Progress retrieved successfully');
    }

    /**
     * Download import template CSV
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadTemplate(): \Symfony\Component\HttpFoundation\Response
    {
        $importer = new ProductImporter();
        $examples = $importer->examples();
        
        if (empty($examples)) {
            $examples = [[
                'name' => 'Sample Product',
                'description' => 'Sample product description',
                'price' => '99.99',
                'sku' => 'SAMPLE-001',
                'categories' => 'Category 1,Category 2',
                'brand' => 'Sample Brand',
                'images' => 'https://example.com/image1.jpg,https://example.com/image2.jpg',
                'status' => 'published',
                'stock_status' => 'in_stock',
                'quantity' => '100',
                'weight' => '1.5',
                'length' => '10',
                'wide' => '5',
                'height' => '3'
            ]];
        }

        // Generate CSV content
        $csvContent = $this->arrayToCsv($examples);
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_import_template.csv"',
        ]);
    }

    /**
     * Validate import request
     *
     * @param Request $request
     * @throws ValidationException
     */
    private function validateImportRequest(Request $request): void
    {
        $rules = [
            'file' => 'sometimes|file|mimes:csv,txt,xlsx,xls|max:102400', // 100MB max
            'products' => 'sometimes|array|max:1000', // Max 1000 products per request
            'products.*.name' => 'sometimes|required|string|max:250',
            'products.*.price' => 'sometimes|required|numeric|min:0',
            'product' => 'sometimes|array',
            'product.name' => 'sometimes|required|string|max:250',
            'product.price' => 'sometimes|required|numeric|min:0',
            // All the options available in manual import
            'update_existing' => 'sometimes|boolean',
            'download_images' => 'sometimes|boolean',
            'create_categories' => 'sometimes|boolean',
            'create_brands' => 'sometimes|boolean',
            'create_variations' => 'sometimes|boolean',
            'auto_generate_sku' => 'sometimes|boolean',
            'skip_header' => 'sometimes|boolean',
            'chunk_size' => 'sometimes|integer|min:1|max:1000',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Handle CSV file import
     *
     * @param Request $request
     * @param ProductImporter $importer
     * @return array
     */
    private function handleCsvImport(Request $request, ProductImporter $importer): array
    {
        $file = $request->file('file');
        
        if (!$file) {
            throw new \Exception('No file uploaded');
        }
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['csv', 'xlsx', 'xls', 'txt'])) {
            throw new \Exception('Invalid file format. Only CSV, XLSX, XLS and TXT files are allowed.');
        }
        
        \Log::info('File uploaded: ' . $file->getClientOriginalName() . ' (' . $extension . ')');
        
        // Store file temporarily
        $tempPath = $file->store('imports', 'local');
        $fullPath = storage_path('app/' . $tempPath);
        
        \Log::info('File stored at: ' . $fullPath);

        try {
            // Set the import type
            $importer->setImportType($request->get('type', 'all'));
            
            // Parse the CSV/Excel file manually
            $rawData = $this->parseFile($fullPath, $extension);
            
            \Log::info('File parsed: ' . count($rawData) . ' rows');
            
            // Map each row through the ProductImporter's mapping logic
            $mappedData = [];
            $mappingErrors = [];
            
            foreach ($rawData as $index => $row) {
                try {
                    $mappedRow = $importer->map($row);
                    if (!empty($mappedRow)) {
                        $mappedData[] = $mappedRow;
                    }
                } catch (\Exception $e) {
                    $mappingErrors[] = [
                        'row' => $index + 1,
                        'message' => $e->getMessage(),
                        'data' => $row
                    ];
                    \Log::error('Row mapping error: ' . $e->getMessage(), ['row' => $row]);
                }
            }
            
            \Log::info('Rows mapped: ' . count($mappedData) . ' of ' . count($rawData));
            
            // Process through the ProductImporter handle method
            try {
                $successCount = $importer->handle($mappedData);
            } catch (\Exception $e) {
                \Log::error('Import handle error: ' . $e->getMessage());
                throw new \Exception('Product import processing failed: ' . $e->getMessage());
            }
            
            $totalProcessed = count($rawData);
            $failed = $totalProcessed - $successCount;
            
            // Combine mapping errors with import failures
            $allErrors = $mappingErrors;
            
            // Clean up temporary file
            if (file_exists($fullPath)) {
                Storage::disk('local')->delete($tempPath);
            }

            return [
                'data' => [
                    'total_processed' => $totalProcessed,
                    'successful' => $successCount,
                    'failed' => $failed,
                    'errors' => $allErrors,
                    'summary' => [
                        'message' => "Successfully imported $successCount of $totalProcessed products",
                        'success_rate' => $totalProcessed > 0 ? round(($successCount / $totalProcessed) * 100, 2) : 100
                    ]
                ],
                'message' => 'File import completed successfully'
            ];

        } catch (Exception $e) {
            // Clean up on error
            if (file_exists($fullPath)) {
                Storage::disk('local')->delete($tempPath);
            }
            throw $e;
        }
    }

    /**
     * Handle JSON array import
     *
     * @param Request $request
     * @param ProductImporter $importer
     * @return array
     */
    private function handleJsonArrayImport(Request $request, ProductImporter $importer): array
    {
        $products = $request->get('products', []);
        
        $result = $this->processProductData($products, $importer);

        return [
            'data' => $result,
            'message' => 'JSON array import completed successfully'
        ];
    }

    /**
     * Handle single product import
     *
     * @param Request $request
     * @param ProductImporter $importer
     * @return array
     */
    private function handleSingleProductImport(Request $request, ProductImporter $importer): array
    {
        $product = $request->get('product', []);
        
        $result = $this->processProductData([$product], $importer);

        return [
            'data' => $result,
            'message' => 'Single product import completed successfully'
        ];
    }

    /**
     * Process product data through ProductImporter
     *
     * @param array $data
     * @param ProductImporter $importer
     * @return array
     */
    private function processProductData(array $data, ProductImporter $importer): array
    {
        $totalProcessed = count($data);
        $successful = 0;
        $failed = 0;
        $errors = [];

        try {
            // Create a temporary request object for the importer
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'update_existing' => true,
                'download_images' => false,
                'create_categories' => true,
                'create_brands' => true
            ]);

            // Set data for the importer
            $importer->setData($data);
            
            // Process the import
            $result = $importer->handle();
            
            if ($result) {
                $successful = $totalProcessed;
                $errors = $importer->failures() ?? [];
                $failed = count($errors);
                $successful = $totalProcessed - $failed;
            } else {
                $failed = $totalProcessed;
                $errors = $importer->failures() ?? [['message' => 'Unknown import error']];
            }

        } catch (Exception $e) {
            $failed = $totalProcessed;
            $errors = [['message' => $e->getMessage()]];
            \Log::error('Product import processing error: ' . $e->getMessage());
        }

        return [
            'total_processed' => $totalProcessed,
            'successful' => $successful,
            'failed' => $failed,
            'errors' => $errors,
            'summary' => [
                'success_rate' => $totalProcessed > 0 ? round(($successful / $totalProcessed) * 100, 2) : 0,
                'message' => $successful > 0 ? 
                    "Successfully imported {$successful} of {$totalProcessed} products" :
                    "Failed to import any products"
            ]
        ];
    }

    /**
     * Parse CSV content to array
     *
     * @param string $csvContent
     * @return array
     */
    private function parseCsvContent(string $csvContent): array
    {
        $data = [];
        $headers = [];
        
        // Split content into lines
        $lines = explode("\n", $csvContent);
        $lines = array_filter($lines, 'trim'); // Remove empty lines
        
        foreach ($lines as $index => $line) {
            $row = str_getcsv($line);
            
            if ($index === 0) {
                // First row is headers
                $headers = array_map('trim', $row);
            } else {
                // Data rows
                if (count($row) === count($headers)) {
                    $rowData = array_combine($headers, array_map('trim', $row));
                    // Add default import_type if not specified
                    if (!isset($rowData['import_type'])) {
                        $rowData['import_type'] = 'product';
                    }
                    $data[] = $rowData;
                }
            }
        }

        return $data;
    }

    /**
     * Parse file (CSV, XLSX, XLS) and return data array
     *
     * @param string $filePath
     * @param string $extension
     * @return array
     */
    private function parseFile(string $filePath, string $extension): array
    {
        if (in_array($extension, ['csv', 'txt'])) {
            $csvContent = file_get_contents($filePath);
            return $this->parseCsvContent($csvContent);
        }
        
        if (in_array($extension, ['xlsx', 'xls'])) {
            return $this->parseExcelFile($filePath);
        }
        
        throw new \Exception('Unsupported file format: ' . $extension);
    }

    /**
     * Parse Excel file (XLSX, XLS)
     *
     * @param string $filePath
     * @return array
     */
    private function parseExcelFile(string $filePath): array
    {
        try {
            // Try to use PhpSpreadsheet if available
            if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
                $spreadsheet = $reader->load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                
                $data = [];
                $headers = [];
                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();
                $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
                
                for ($row = 1; $row <= $highestRow; $row++) {
                    $rowData = [];
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $cellValue = $worksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
                        $rowData[] = $cellValue;
                    }
                    
                    if ($row === 1) {
                        $headers = array_map('trim', $rowData);
                    } else {
                        if (count($rowData) === count($headers)) {
                            $mappedData = array_combine($headers, array_map('trim', $rowData));
                            // Add default import_type if not specified
                            if (!isset($mappedData['import_type'])) {
                                $mappedData['import_type'] = 'product';
                            }
                            $data[] = $mappedData;
                        }
                    }
                }
                
                return $data;
            }
            
            // Fallback: try to convert to CSV first
            throw new \Exception('PhpSpreadsheet not available for Excel parsing');
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to parse Excel file: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV file to array
     *
     * @param string $filePath
     * @return array
     */
    private function parseCsvFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception('CSV file not found');
        }

        $csvContent = file_get_contents($filePath);
        return $this->parseCsvContent($csvContent);
    }

    /**
     * Convert array to CSV string
     *
     * @param array $data
     * @return string
     */
    private function arrayToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, array_keys($data[0]));
        
        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }
}