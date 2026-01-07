<?php

namespace Botble\MultiBranchInventory\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Product;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EcommerceIntegrationDemoController extends BaseController
{
    /**
     * Show ecommerce products with branch inventory integration
     */
    public function showProductsWithBranchInventory(Request $request)
    {
        // Get all active branches
        $branches = Branch::where('status', 'active')->get();
        
        // Get products with their branch inventory
        $products = Product::with([
            'branchInventory' => function ($query) {
                $query->with('branch');
            }
        ])
        ->where('status', 'published')
        ->paginate(20);
        
        $data = [
            'branches' => $branches,
            'products' => $products,
            'integration_stats' => $this->getIntegrationStats(),
        ];
        
        return view('plugins/multi-branch-inventory::integration.demo', $data);
    }
    
    /**
     * Get integration statistics
     */
    private function getIntegrationStats(): array
    {
        return [
            'total_products' => Product::count(),
            'products_with_branch_inventory' => Product::whereHas('branchInventory')->count(),
            'total_branches' => Branch::count(),
            'active_branches' => Branch::where('status', 'active')->count(),
            'total_inventory_records' => BranchInventory::count(),
            'low_stock_items' => BranchInventory::whereColumn('quantity_on_hand', '<=', 'minimum_stock')->count(),
            'out_of_stock_items' => BranchInventory::where('quantity_on_hand', 0)->count(),
            'total_inventory_value' => BranchInventory::sum(DB::raw('quantity_on_hand * cost_price')),
        ];
    }
    
    /**
     * Demo API endpoint showing product availability across branches
     */
    public function getProductAvailabilityAcrossBranches($productId)
    {
        $product = Product::with(['branchInventory.branch'])->findOrFail($productId);
        
        $availability = [];
        
        foreach ($product->branchInventory as $inventory) {
            $availability[] = [
                'branch_id' => $inventory->branch->id,
                'branch_name' => $inventory->branch->name,
                'branch_code' => $inventory->branch->code,
                'quantity_available' => $inventory->quantity_available,
                'selling_price' => $inventory->selling_price ?: $product->price,
                'is_available' => $inventory->isAvailable(),
                'visible_online' => $inventory->isVisibleOnline(),
                'visible_in_pos' => $inventory->isVisibleInPos(),
                'stock_status' => $inventory->quantity_available > 0 ? 'in_stock' : 'out_of_stock',
                'low_stock_warning' => $inventory->quantity_on_hand <= $inventory->minimum_stock,
            ];
        }
        
        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'base_price' => $product->price,
            ],
            'branch_availability' => $availability,
            'summary' => [
                'total_branches' => count($availability),
                'branches_in_stock' => collect($availability)->where('is_available', true)->count(),
                'branches_out_of_stock' => collect($availability)->where('is_available', false)->count(),
                'total_available_quantity' => collect($availability)->sum('quantity_available'),
                'lowest_price' => collect($availability)->min('selling_price'),
                'highest_price' => collect($availability)->max('selling_price'),
            ]
        ]);
    }
    
    /**
     * Sync ecommerce product with branch inventories
     */
    public function syncProductWithBranches($productId)
    {
        $product = Product::findOrFail($productId);
        $branches = Branch::where('status', 'active')->get();
        
        $synced = [];
        
        foreach ($branches as $branch) {
            // Check if inventory record exists
            $inventory = BranchInventory::where('product_id', $productId)
                ->where('branch_id', $branch->id)
                ->first();
                
            if (!$inventory) {
                // Create new inventory record
                $inventory = BranchInventory::create([
                    'branch_id' => $branch->id,
                    'product_id' => $productId,
                    'sku' => $product->sku,
                    'quantity_on_hand' => 0,
                    'quantity_available' => 0,
                    'minimum_stock' => 5, // Default minimum
                    'cost_price' => $product->cost_per_item ?? 0,
                    'selling_price' => $product->price,
                    'visible_online' => true,
                    'visible_in_pos' => true,
                ]);
                
                $synced[] = [
                    'branch' => $branch->name,
                    'action' => 'created',
                    'inventory_id' => $inventory->id,
                ];
            } else {
                // Update existing inventory
                $inventory->update([
                    'sku' => $product->sku,
                    'selling_price' => $product->price,
                ]);
                
                $synced[] = [
                    'branch' => $branch->name,
                    'action' => 'updated',
                    'inventory_id' => $inventory->id,
                ];
            }
        }
        
        return response()->json([
            'message' => 'Product synced with all branches successfully',
            'product' => $product->name,
            'synced_branches' => $synced,
        ]);
    }
    
    /**
     * Get low stock report across all branches
     */
    public function getLowStockReport()
    {
        $lowStockItems = BranchInventory::with(['product', 'branch'])
            ->whereColumn('quantity_on_hand', '<=', 'minimum_stock')
            ->get()
            ->groupBy('product_id');
            
        $report = [];
        
        foreach ($lowStockItems as $productId => $items) {
            $product = $items->first()->product;
            $branches = [];
            
            foreach ($items as $item) {
                $branches[] = [
                    'branch_name' => $item->branch->name,
                    'current_stock' => $item->quantity_on_hand,
                    'minimum_stock' => $item->minimum_stock,
                    'deficit' => $item->minimum_stock - $item->quantity_on_hand,
                ];
            }
            
            $report[] = [
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                ],
                'affected_branches' => $branches,
                'total_deficit' => collect($branches)->sum('deficit'),
            ];
        }
        
        return response()->json([
            'low_stock_products' => $report,
            'total_products_affected' => count($report),
            'summary' => [
                'total_low_stock_items' => $lowStockItems->flatten()->count(),
                'branches_affected' => $lowStockItems->flatten()->pluck('branch.name')->unique()->count(),
            ]
        ]);
    }

    /**
     * Demonstrate real-time inventory synchronization
     */
    public function demoInventorySync()
    {
        $syncService = app(\Botble\MultiBranchInventory\Services\EcommerceInventorySyncService::class);
        
        // Get sample products for demonstration
        $products = Product::with('branchInventory.branch')
            ->whereHas('branchInventory')
            ->take(5)
            ->get();

        $syncResults = [];

        foreach ($products as $product) {
            // Get current sync status
            $status = $syncService->getSyncStatus($product);
            
            // Perform sync if needed
            if (!$status['is_in_sync']) {
                $syncResult = $syncService->syncBranchesToProduct($product);
                $status['sync_performed'] = true;
                $status['sync_result'] = $syncResult;
            } else {
                $status['sync_performed'] = false;
                $status['message'] = 'Already in sync';
            }
            
            $syncResults[] = $status;
        }

        return response()->json([
            'demo_type' => 'inventory_synchronization',
            'timestamp' => now()->toDateTimeString(),
            'products_checked' => count($products),
            'sync_results' => $syncResults,
            'summary' => [
                'products_in_sync' => collect($syncResults)->where('is_in_sync', true)->count(),
                'products_synced' => collect($syncResults)->where('sync_performed', true)->count(),
                'total_quantity_differences' => collect($syncResults)->sum('quantity_difference'),
            ],
            'how_it_works' => [
                '1' => 'Each product maintains inventory across multiple branches',
                '2' => 'Main product quantity = sum of all branch available quantities',
                '3' => 'When branch stock changes, main product auto-updates',
                '4' => 'POS-only products excluded from online totals',
                '5' => 'Real-time sync happens on every sale/stock movement',
            ]
        ]);
    }

    /**
     * Simulate a sale and show inventory sync
     */
    public function simulateSaleSync(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:ec_products,id',
            'branch_id' => 'required|exists:mbi_branches,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $syncService = app(\Botble\MultiBranchInventory\Services\EcommerceInventorySyncService::class);
        $product = Product::find($request->product_id);
        
        // Get status before sale
        $statusBefore = $syncService->getSyncStatus($product);

        // Simulate sale
        $saleData = [
            'branch_id' => $request->branch_id,
            'order_id' => 'DEMO-' . time(),
            'items' => [
                [
                    'product_id' => $request->product_id,
                    'quantity' => $request->quantity,
                ]
            ],
            'update_main_product' => true,
        ];

        $saleResult = $syncService->syncInventoryFromSale($saleData);
        
        // Get status after sale
        $statusAfter = $syncService->getSyncStatus($product->fresh());

        return response()->json([
            'simulation_type' => 'sale_inventory_sync',
            'sale_details' => $saleData,
            'status_before' => $statusBefore,
            'sale_processing' => $saleResult,
            'status_after' => $statusAfter,
            'changes' => [
                'main_product_quantity_change' => $statusAfter['main_product_quantity'] - $statusBefore['main_product_quantity'],
                'branch_quantity_change' => -$request->quantity,
                'sync_successful' => $saleResult[0]['success'] ?? false,
            ],
            'explanation' => [
                'what_happened' => "Sale of {$request->quantity} units reduced branch inventory and automatically updated main product quantity",
                'branch_impact' => "Branch inventory reduced by {$request->quantity} units",
                'main_product_impact' => "Main product quantity automatically recalculated from all branches",
                'real_time' => "This sync happens instantly on every sale/stock movement",
            ]
        ]);
    }
}