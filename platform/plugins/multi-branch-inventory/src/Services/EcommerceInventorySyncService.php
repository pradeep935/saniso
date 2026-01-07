<?php

namespace Botble\MultiBranchInventory\Services;

use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EcommerceInventorySyncService
{
    /**
     * Sync ecommerce product with branch inventories
     * This is the main synchronization service
     */
    public function syncProductToBranches(Product $product, array $options = []): array
    {
        $results = [
            'success' => true,
            'synced_branches' => [],
            'errors' => [],
            'total_quantity_synced' => 0,
        ];

        try {
            DB::beginTransaction();

            $branches = Branch::where('status', 'active')->get();

            foreach ($branches as $branch) {
                try {
                    $branchInventory = $this->syncProductToBranch($product, $branch, $options);
                    
                    $results['synced_branches'][] = [
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'action' => $branchInventory['action'],
                        'quantity_updated' => $branchInventory['quantity'],
                        'previous_quantity' => $branchInventory['previous_quantity'] ?? 0,
                        'new_quantity' => $branchInventory['new_quantity'],
                    ];

                    $results['total_quantity_synced'] += $branchInventory['quantity'];

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'branch_id' => $branch->id,
                        'branch_name' => $branch->name,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $results['success'] = false;
            $results['errors'][] = ['general' => $e->getMessage()];
        }

        return $results;
    }

    /**
     * Sync single product to single branch
     */
    private function syncProductToBranch(Product $product, Branch $branch, array $options = []): array
    {
        $branchInventory = BranchInventory::where([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
        ])->first();

        if (!$branchInventory) {
            // Create new inventory record
            $branchInventory = BranchInventory::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'sku' => $product->sku,
                'ean' => $product->barcode ?? '',
                'quantity_on_hand' => $options['initial_quantity'] ?? 0,
                'quantity_available' => $options['initial_quantity'] ?? 0,
                'minimum_stock' => $options['minimum_stock'] ?? 5,
                'cost_price' => $product->cost_per_item ?? 0,
                'selling_price' => $product->price,
                'visible_online' => $options['visible_online'] ?? true,
                'visible_in_pos' => $options['visible_in_pos'] ?? true,
                'only_visible_in_pos' => $options['pos_only'] ?? false,
            ]);

            return [
                'action' => 'created',
                'quantity' => $options['initial_quantity'] ?? 0,
                'new_quantity' => $branchInventory->quantity_on_hand,
            ];
        } else {
            // Update existing inventory
            $previousQuantity = $branchInventory->quantity_on_hand;
            
            // Update product information
            $branchInventory->update([
                'sku' => $product->sku,
                'ean' => $product->barcode ?? $branchInventory->ean,
                'cost_price' => $product->cost_per_item ?? $branchInventory->cost_price,
                'selling_price' => $options['keep_local_price'] ? 
                    $branchInventory->selling_price : $product->price,
            ]);

            // Optionally sync quantities
            if ($options['sync_quantities'] ?? false) {
                $targetQuantity = $options['target_quantity'] ?? $product->quantity;
                if ($targetQuantity !== $previousQuantity) {
                    $branchInventory->updateStock(
                        $targetQuantity,
                        'set',
                        'Synced from main product: ' . ($product->name ?? $product->sku)
                    );
                }
            }

            return [
                'action' => 'updated',
                'quantity' => $branchInventory->quantity_on_hand - $previousQuantity,
                'previous_quantity' => $previousQuantity,
                'new_quantity' => $branchInventory->quantity_on_hand,
            ];
        }
    }

    /**
     * Sync branch inventories back to main product
     * This updates the main ecommerce product quantity based on branch totals
     */
    public function syncBranchesToProduct(Product $product, array $options = []): array
    {
        $branchInventories = BranchInventory::where('product_id', $product->id)
            ->where('visible_online', true)
            ->where('only_visible_in_pos', false)
            ->get();

        $totalQuantity = $branchInventories->sum('quantity_available');
        $previousQuantity = $product->quantity;

        // Update main product
        $product->update([
            'quantity' => $totalQuantity,
            'stock_status' => $totalQuantity > 0 ? 'in_stock' : 'out_of_stock',
        ]);

        // Log the sync
        Log::info("Product inventory synced", [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $totalQuantity,
            'branches_involved' => $branchInventories->pluck('branch.name')->toArray(),
        ]);

        return [
            'success' => true,
            'product_id' => $product->id,
            'previous_quantity' => $previousQuantity,
            'new_quantity' => $totalQuantity,
            'quantity_difference' => $totalQuantity - $previousQuantity,
            'branches_involved' => $branchInventories->count(),
            'branch_details' => $branchInventories->map(function ($inventory) {
                return [
                    'branch_name' => $inventory->branch->name,
                    'quantity_available' => $inventory->quantity_available,
                    'quantity_on_hand' => $inventory->quantity_on_hand,
                ];
            })->toArray(),
        ];
    }

    /**
     * Real-time inventory sync when sale occurs
     */
    public function syncInventoryFromSale(array $saleData): array
    {
        $results = [];

        foreach ($saleData['items'] as $item) {
            $branchInventory = BranchInventory::where([
                'branch_id' => $saleData['branch_id'],
                'product_id' => $item['product_id'],
            ])->first();

            if ($branchInventory) {
                $success = $branchInventory->updateStock(
                    $item['quantity'],
                    'subtract',
                    'Sale - Order #' . ($saleData['order_id'] ?? 'N/A')
                );

                $results[] = [
                    'product_id' => $item['product_id'],
                    'branch_id' => $saleData['branch_id'],
                    'quantity_sold' => $item['quantity'],
                    'success' => $success,
                    'remaining_stock' => $branchInventory->fresh()->quantity_available,
                ];

                // Update main product quantity
                if ($success && ($saleData['update_main_product'] ?? true)) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $this->syncBranchesToProduct($product);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Bulk sync all products with their branch inventories
     */
    public function bulkSyncAllProducts(array $options = []): array
    {
        $batchSize = $options['batch_size'] ?? 100;
        $onlyOutOfSync = $options['only_out_of_sync'] ?? false;
        
        $results = [
            'total_processed' => 0,
            'total_synced' => 0,
            'errors' => [],
        ];

        Product::with('branchInventory')
            ->when($onlyOutOfSync, function ($query) {
                // Only process products where quantities don't match
                $query->whereHas('branchInventory', function ($subQuery) {
                    $subQuery->havingRaw('SUM(quantity_available) != (SELECT quantity FROM ec_products WHERE id = product_id)');
                });
            })
            ->chunk($batchSize, function ($products) use (&$results) {
                foreach ($products as $product) {
                    try {
                        $syncResult = $this->syncBranchesToProduct($product);
                        
                        if ($syncResult['quantity_difference'] != 0) {
                            $results['total_synced']++;
                        }
                        
                        $results['total_processed']++;

                    } catch (\Exception $e) {
                        $results['errors'][] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'error' => $e->getMessage(),
                        ];
                    }
                }
            });

        return $results;
    }

    /**
     * Get synchronization status for a product
     */
    public function getSyncStatus(Product $product): array
    {
        $branchInventories = $product->branchInventory()
            ->with('branch')
            ->get();

        $totalBranchQuantity = $branchInventories
            ->where('visible_online', true)
            ->where('only_visible_in_pos', false)
            ->sum('quantity_available');

        $isInSync = $product->quantity == $totalBranchQuantity;

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'main_product_quantity' => $product->quantity,
            'total_branch_quantity' => $totalBranchQuantity,
            'quantity_difference' => $totalBranchQuantity - $product->quantity,
            'is_in_sync' => $isInSync,
            'last_synced' => $product->updated_at,
            'branch_breakdown' => $branchInventories->map(function ($inventory) {
                return [
                    'branch_id' => $inventory->branch->id,
                    'branch_name' => $inventory->branch->name,
                    'quantity_on_hand' => $inventory->quantity_on_hand,
                    'quantity_available' => $inventory->quantity_available,
                    'quantity_reserved' => $inventory->quantity_reserved,
                    'visible_online' => $inventory->visible_online,
                    'pos_only' => $inventory->only_visible_in_pos,
                    'last_updated' => $inventory->updated_at,
                ];
            })->toArray(),
        ];
    }
}