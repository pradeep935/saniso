<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\DataSynchronize\Exporter\Exporter;
use Botble\DataSynchronize\Http\Controllers\ExportController;
use Botble\DataSynchronize\Http\Requests\ExportRequest;
use Botble\Ecommerce\Exporters\ProductExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportProductController extends ExportController
{
    protected function getExporter(): Exporter
    {
        $exporter = ProductExporter::make();

        // Only apply filters if at least one is present and non-empty
        $filters = [];
        $hasFilters = false;

        if (request()->has('filter_categories') && !empty(request('filter_categories'))) {
            $filters['categories'] = array_filter(request('filter_categories'));
            $hasFilters = true;
        }
        if (request()->has('filter_brands') && !empty(request('filter_brands'))) {
            $filters['brands'] = array_filter(request('filter_brands'));
            $hasFilters = true;
        }
        if (request()->has('filter_status') && !empty(request('filter_status'))) {
            $filters['status'] = array_filter(request('filter_status'));
            $hasFilters = true;
        }
        if (request()->has('filter_product_types') && !empty(request('filter_product_types'))) {
            $filters['product_types'] = array_filter(request('filter_product_types'));
            $hasFilters = true;
        }
        if (request()->has('filter_stock_status') && !empty(request('filter_stock_status'))) {
            $filters['stock_status'] = array_filter(request('filter_stock_status'));
            $hasFilters = true;
        }
        if (request()->has('filter_is_featured') && request('filter_is_featured') !== null && request('filter_is_featured') !== '') {
            $filters['is_featured'] = request('filter_is_featured');
            $hasFilters = true;
        }

        if ($hasFilters) {
            $exporter->setFilters($filters);
        } else {
            // Debug: log/dump filters if not added
            \Log::info('ExportProductController: No filters applied to export', [
                'request' => request()->all(),
                'filters' => $filters
            ]);
            $exporter->setFilters([]); // No filters, export all
        }

        return $exporter;
    }

    protected function streamingExport(Exporter $exporter, ExportRequest $request): StreamedResponse
    {
        $fileName = str_replace('.xlsx', '.csv', $exporter->getExportFileName());

        return response()->streamDownload(function () use ($exporter, $request) {
            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            if ($request->has('columns')) {
                $exporter->acceptedColumns($request->input('columns'));
            }

            $headers = $exporter->headings();
            fputcsv($handle, $headers);

            DB::disableQueryLog();

            if ($request->has('include_variations') && method_exists($exporter, 'setIncludeVariations')) {
                $exporter->setIncludeVariations($request->boolean('include_variations'));
            }

            // Auto-enable streaming and optimized queries for very large exports
            if (method_exists($exporter, 'getProductsCount') && method_exists($exporter, 'getVariationsCount')) {
                try {
                    $productCount = $exporter->getProductsCount();
                    $variationCount = $exporter->getIncludeVariations() ? $exporter->getVariationsCount() : 0;
                    $totalCount = $productCount + $variationCount;

                    // Threshold can be tuned; 10k total rows is a safe default for streaming
                    if ($totalCount > 10000) {
                        if (method_exists($exporter, 'enableStreamingMode')) {
                            $exporter->enableStreamingMode(true);
                        }

                        if (method_exists($exporter, 'setOptimizeQueries')) {
                            $exporter->setOptimizeQueries(true);
                        }
                    }
                } catch (\Exception $e) {
                    // Non-fatal: if counts fail, fall back to request flags only
                }
            }

            if ($request->has('use_streaming') && method_exists($exporter, 'enableStreamingMode')) {
                $exporter->enableStreamingMode($request->boolean('use_streaming'));
            }

            if ($request->has('optimize_queries') && method_exists($exporter, 'setOptimizeQueries')) {
                $exporter->setOptimizeQueries($request->boolean('optimize_queries'));
            }

            if (method_exists($exporter, 'isStreamingMode') && $exporter->isStreamingMode() && method_exists($exporter, 'streamingGenerator')) {
                foreach ($exporter->streamingGenerator() as $item) {
                    $row = $exporter->map($item);
                    fputcsv($handle, $row);

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            } else {
                $collection = $exporter->collection();
                foreach ($collection as $item) {
                    $row = $exporter->map($item);
                    fputcsv($handle, $row);
                }
            }

            DB::enableQueryLog();

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    public function getFilteredCount(Request $request)
    {
        try {
            $exporter = ProductExporter::make();
            
            // Apply filters from request
            $filters = [];
            
            if ($request->has('filter_categories') && !empty($request->input('filter_categories'))) {
                $filters['categories'] = array_filter($request->input('filter_categories'));
            }
            
            if ($request->has('filter_brands') && !empty($request->input('filter_brands'))) {
                $filters['brands'] = array_filter($request->input('filter_brands'));
            }
            
            if ($request->has('filter_status') && !empty($request->input('filter_status'))) {
                $filters['status'] = array_filter($request->input('filter_status'));
            }
            
            if ($request->has('filter_product_types') && !empty($request->input('filter_product_types'))) {
                $filters['product_types'] = array_filter($request->input('filter_product_types'));
            }
            
            if ($request->has('filter_stock_status') && !empty($request->input('filter_stock_status'))) {
                $filters['stock_status'] = array_filter($request->input('filter_stock_status'));
            }
            
            if ($request->has('filter_is_featured') && $request->input('filter_is_featured') !== null && $request->input('filter_is_featured') !== '') {
                $filters['is_featured'] = $request->input('filter_is_featured');
            }
            
            $exporter->setFilters($filters);
            
            // Set include variations
            $includeVariations = $request->boolean('include_variations', true);
            $exporter->setIncludeVariations($includeVariations);
            
            // Get counts
            $productCount = $exporter->getProductsCount();
            $variationCount = $includeVariations ? $exporter->getVariationsCount() : 0;
            $totalCount = $productCount + $variationCount;
            
            return response()->json([
                'success' => true,
                'count' => $totalCount,
                'breakdown' => [
                    'products' => $productCount,
                    'variations' => $variationCount,
                    'total' => $totalCount
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating product count: ' . $e->getMessage()
            ], 500);
        }
    }
}
