<?php

namespace Botble\MultiBranchInventory\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\MultiBranchInventory\Models\TemporaryProduct;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemporaryProductController extends BaseController
{
    /**
     * Display temporary products
     */
    public function index(Request $request)
    {
        $branches = Branch::where('status', 'active')->get();
        
        $query = TemporaryProduct::with(['branch', 'linkedProduct', 'creator']);

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('ean', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            });
        }

        $temporaryProducts = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('plugins/multi-branch-inventory::temporary-products.index', 
            compact('temporaryProducts', 'branches'));
    }

    /**
     * Generate a simple SKU for a temporary product when none provided.
     */
    protected function generateSku(string $name, $branchId = null): string
    {
        $prefix = 'NG';
        $namePart = preg_replace('/[^A-Z0-9]/', '', strtoupper(substr($name, 0, 3)));
        $branchPart = $branchId ? 'B' . intval($branchId) : '';
        $rand = mt_rand(100, 999);
        return $prefix . '-' . ($namePart ?: 'X') . '-' . $branchPart . '-' . time() % 100000 . $rand;
    }

    /**
     * Show create form
     */
    public function create()
    {
        $branches = Branch::where('status', 'active')->get();
        
        return view('plugins/multi-branch-inventory::temporary-products.create', compact('branches'));
    }

    /**
     * Store new temporary product
     */
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'name' => 'required|string|max:255',
            'selling_price' => 'nullable|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'ean' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255',
            'product_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'storage_location' => 'nullable|string|max:255',
        ]);

        $temporaryProduct = TemporaryProduct::create([
            'branch_id' => $request->branch_id,
            'name' => $request->name,
            'selling_price' => $request->selling_price ?: 0,
            'quantity' => $request->quantity,
            'ean' => $request->ean,
            // generate SKU if not provided
            'sku' => $request->sku ?: $this->generateSku($request->name, $request->branch_id),
            'product_code' => $request->product_code,
            'description' => $request->description,
            'cost_price' => $request->cost_price ?: 0,
            'storage_location' => $request->storage_location,
            'created_by' => Auth::id(),
            'status' => 'active',
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('temporary-products.index')
            ->with('success', 'Temporary product created successfully!');
    }

    /**
     * Show temporary product
     */
    public function show(TemporaryProduct $temporaryProduct)
    {
        $temporaryProduct->load(['branch', 'linkedProduct', 'creator']);
        
        return view('plugins/multi-branch-inventory::temporary-products.show', compact('temporaryProduct'));
    }

    /**
     * Show edit form
     */
    public function edit(TemporaryProduct $temporaryProduct)
    {
        $branches = Branch::where('status', 'active')->get();
        
        return view('plugins/multi-branch-inventory::temporary-products.edit', 
            compact('temporaryProduct', 'branches'));
    }

    /**
     * Update temporary product
     */
    public function update(Request $request, TemporaryProduct $temporaryProduct)
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'ean' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:255',
            'product_code' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cost_price' => 'nullable|numeric|min:0',
            'storage_location' => 'nullable|string|max:255',
            'status' => 'required|in:active,sold_out,converted,inactive',
        ]);

        $temporaryProduct->update($request->only([
            'branch_id', 'name', 'selling_price', 'quantity', 'ean', 'sku',
            'product_code', 'description', 'cost_price', 'storage_location', 
            'status', 'notes'
        ]));

        return back()->with('success', 'Temporary product updated successfully!');
    }

    /**
     * Sell quantity from temporary product (POS integration)
     */
    public function sellQuantity(Request $request, TemporaryProduct $temporaryProduct)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($temporaryProduct->sellQuantity($request->quantity)) {
            return response()->json([
                'success' => true,
                'message' => 'Sale recorded successfully',
                'remaining_quantity' => $temporaryProduct->quantity,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Insufficient quantity or product not active',
        ], 400);
    }

    /**
     * Get temporary products for branch (POS API)
     */
    public function getBranchProducts(Request $request)
    {
        $branchId = $request->branch_id;
        
        if (!$branchId) {
            return response()->json(['error' => 'Branch ID required'], 400);
        }

        $products = TemporaryProduct::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->select([
                'id', 'name', 'sku', 'ean', 'product_code', 
                'selling_price', 'quantity', 'storage_location'
            ])
            ->get();

        return response()->json($products);
    }

    /**
     * Search temporary products for POS
     */
    public function searchForPos(Request $request)
    {
        $branchId = $request->branch_id;
        $search = $request->search;

        if (!$branchId || !$search) {
            return response()->json([]);
        }

        $products = TemporaryProduct::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('ean', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            })
            ->select([
                'id', 'name', 'sku', 'ean', 'product_code', 
                'selling_price', 'quantity', 'storage_location'
            ])
            ->limit(10)
            ->get();

        return response()->json($products);
    }

    /**
     * Import temporary products from Excel
     */
    public function importExcel(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:mbi_branches,id',
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        // This would typically use a package like Laravel Excel
        // For now, we'll create a placeholder method
        
        return back()->with('info', 'Excel import feature will be implemented with Laravel Excel package.');
    }

    /**
     * Export temporary products to Excel
     */
    public function exportExcel(Request $request)
    {
        $branchId = $request->branch_id;
        
        // This would typically use Laravel Excel to export
        // For now, we'll create a basic CSV export
        
        $products = TemporaryProduct::where('branch_id', $branchId)->get();
        
        $csv = "Name,SKU,EAN,Product Code,Quantity,Cost Price,Selling Price,Status\n";
        
        foreach ($products as $product) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%d,%.2f,%.2f,%s\n",
                $product->name,
                $product->sku,
                $product->ean,
                $product->product_code,
                $product->quantity,
                $product->cost_price ?: 0,
                $product->selling_price,
                $product->status
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="temporary-products-' . date('Y-m-d') . '.csv"',
        ]);
    }

    /**
     * Convert a temporary product into a permanent ecommerce product and create branch inventory.
     */
    public function convert(Request $request, TemporaryProduct $temporaryProduct)
    {
        try {
            DB::transaction(function () use ($temporaryProduct) {
                // Try to find existing product by SKU or barcode to avoid duplicates
                $sku = $temporaryProduct->sku ?: $this->generateSku($temporaryProduct->name, $temporaryProduct->branch_id);
                $barcode = $temporaryProduct->ean ?: $temporaryProduct->product_code;

                $product = Product::where('sku', $sku)
                    ->orWhere('barcode', $barcode)
                    ->first();

                if (!$product) {
                    $product = Product::create([
                        'name' => $temporaryProduct->name,
                        'sku' => $sku,
                        'description' => $temporaryProduct->description,
                        'price' => $temporaryProduct->selling_price ?: 0,
                        'cost_per_item' => $temporaryProduct->cost_price ?: 0,
                        'barcode' => $barcode,
                        'with_storehouse_management' => true,
                        'available_in_pos' => true,
                    ]);
                }

                // Create or update branch inventory for this product
                BranchInventory::updateOrCreate(
                    ['branch_id' => $temporaryProduct->branch_id, 'product_id' => $product->id],
                    [
                        'sku' => $product->sku,
                        'ean' => $product->barcode,
                        'quantity_on_hand' => $temporaryProduct->quantity ?: 0,
                        'quantity_available' => $temporaryProduct->quantity ?: 0,
                        'cost_price' => $temporaryProduct->cost_price ?: 0,
                        'selling_price' => $temporaryProduct->selling_price ?: 0,
                        'visible_in_pos' => true,
                    ]
                );

                // Mark temporary product as converted
                $temporaryProduct->linked_product_id = $product->id;
                $temporaryProduct->status = 'converted';
                $temporaryProduct->save();
            });

            return redirect()->route('temporary-products.index')->with('success', 'Temporary product converted to permanent product.');
        } catch (\Exception $e) {
            \Log::error('Failed to convert temporary product: ' . $e->getMessage());
            return back()->with('error', 'Failed to convert temporary product. Check logs.');
        }
    }
}