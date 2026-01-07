<?php

namespace Botble\MultiBranchInventory\Services;

use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\MultiBranchInventory\Models\TemporaryProduct;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Collection;

class PosIntegrationService
{
    /**
     * Get all available products for POS in a specific branch
     */
    public function getAvailableProductsForBranch(int $branchId): Collection
    {
        $products = collect();

        // Get regular products with branch inventory
        $branchProducts = BranchInventory::with('product')
            ->where('branch_id', $branchId)
            ->where('visible_in_pos', true)
            ->where('quantity_available', '>', 0)
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->product->id,
                    'type' => 'regular',
                    'name' => $inventory->product->name,
                    'sku' => $inventory->sku,
                    'ean' => $inventory->ean,
                    'price' => $inventory->effective_price,
                    'quantity_available' => $inventory->quantity_available,
                    'storage_location' => $inventory->storage_location,
                    'image' => $inventory->product->image,
                ];
            });

        // Get temporary products
        $temporaryProducts = TemporaryProduct::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->get()
            ->map(function ($temp) {
                return [
                    'id' => 'temp_' . $temp->id,
                    'type' => 'temporary',
                    'name' => $temp->name,
                    'sku' => $temp->sku,
                    'ean' => $temp->ean,
                    'price' => $temp->selling_price,
                    'quantity_available' => $temp->quantity,
                    'storage_location' => $temp->storage_location,
                    'image' => null,
                ];
            });

        return $products->concat($branchProducts)->concat($temporaryProducts);
    }

    /**
     * Search products in branch for POS
     */
    public function searchProductsInBranch(int $branchId, string $search): Collection
    {
        $products = collect();

        // Search regular products
        $branchProducts = BranchInventory::with('product')
            ->where('branch_id', $branchId)
            ->where('visible_in_pos', true)
            ->where('quantity_available', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('ean', 'like', "%{$search}%")
                  ->orWhereHas('product', function ($productQuery) use ($search) {
                      $productQuery->where('name', 'like', "%{$search}%");
                  });
            })
            ->limit(10)
            ->get()
            ->map(function ($inventory) {
                return [
                    'id' => $inventory->product->id,
                    'type' => 'regular',
                    'name' => $inventory->product->name,
                    'sku' => $inventory->sku,
                    'ean' => $inventory->ean,
                    'price' => $inventory->effective_price,
                    'quantity_available' => $inventory->quantity_available,
                    'storage_location' => $inventory->storage_location,
                ];
            });

        // Search temporary products
        $temporaryProducts = TemporaryProduct::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('ean', 'like', "%{$search}%")
                  ->orWhere('product_code', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($temp) {
                return [
                    'id' => 'temp_' . $temp->id,
                    'type' => 'temporary',
                    'name' => $temp->name,
                    'sku' => $temp->sku,
                    'ean' => $temp->ean,
                    'price' => $temp->selling_price,
                    'quantity_available' => $temp->quantity,
                    'storage_location' => $temp->storage_location,
                ];
            });

        return $products->concat($branchProducts)->concat($temporaryProducts);
    }

    /**
     * Process POS sale and update inventory
     */
    public function processSale(int $branchId, array $items): array
    {
        $results = [];
        $errors = [];

        foreach ($items as $item) {
            $itemId = $item['id'];
            $quantity = $item['quantity'];

            if (str_starts_with($itemId, 'temp_')) {
                // Handle temporary product
                $tempId = str_replace('temp_', '', $itemId);
                $temporaryProduct = TemporaryProduct::find($tempId);

                if ($temporaryProduct && $temporaryProduct->sellQuantity($quantity)) {
                    $results[] = [
                        'id' => $itemId,
                        'type' => 'temporary',
                        'success' => true,
                        'remaining_quantity' => $temporaryProduct->quantity,
                    ];
                } else {
                    $errors[] = [
                        'id' => $itemId,
                        'error' => 'Insufficient temporary product quantity',
                    ];
                }
            } else {
                // Handle regular product
                $branchInventory = BranchInventory::where([
                    'branch_id' => $branchId,
                    'product_id' => $itemId,
                ])->first();

                if ($branchInventory && $branchInventory->updateStock($quantity, 'subtract', 'POS Sale')) {
                    $results[] = [
                        'id' => $itemId,
                        'type' => 'regular',
                        'success' => true,
                        'remaining_quantity' => $branchInventory->quantity_available,
                    ];
                } else {
                    $errors[] = [
                        'id' => $itemId,
                        'error' => 'Insufficient product quantity',
                    ];
                }
            }
        }

        return [
            'success' => empty($errors),
            'results' => $results,
            'errors' => $errors,
        ];
    }

    /**
     * Check product availability in branch
     */
    public function checkProductAvailability(int $branchId, $productIdentifier, int $quantity = 1): array
    {
        // Try to find by product ID first
        if (is_numeric($productIdentifier)) {
            $branchInventory = BranchInventory::where([
                'branch_id' => $branchId,
                'product_id' => $productIdentifier,
            ])->first();

            if ($branchInventory) {
                return [
                    'available' => $branchInventory->quantity_available >= $quantity,
                    'quantity_available' => $branchInventory->quantity_available,
                    'price' => $branchInventory->effective_price,
                    'type' => 'regular',
                ];
            }
        }

        // Try to find by EAN/SKU
        $branchInventory = BranchInventory::where('branch_id', $branchId)
            ->where(function ($q) use ($productIdentifier) {
                $q->where('ean', $productIdentifier)
                  ->orWhere('sku', $productIdentifier);
            })
            ->first();

        if ($branchInventory) {
            return [
                'available' => $branchInventory->quantity_available >= $quantity,
                'quantity_available' => $branchInventory->quantity_available,
                'price' => $branchInventory->effective_price,
                'type' => 'regular',
                'product_id' => $branchInventory->product_id,
            ];
        }

        // Check temporary products
        $temporaryProduct = TemporaryProduct::where('branch_id', $branchId)
            ->where('status', 'active')
            ->where(function ($q) use ($productIdentifier) {
                $q->where('ean', $productIdentifier)
                  ->orWhere('sku', $productIdentifier)
                  ->orWhere('product_code', $productIdentifier);
            })
            ->first();

        if ($temporaryProduct) {
            return [
                'available' => $temporaryProduct->quantity >= $quantity,
                'quantity_available' => $temporaryProduct->quantity,
                'price' => $temporaryProduct->selling_price,
                'type' => 'temporary',
                'temp_id' => $temporaryProduct->id,
            ];
        }

        return [
            'available' => false,
            'quantity_available' => 0,
            'price' => 0,
            'type' => null,
        ];
    }

    /**
     * Get low stock alerts for branch
     */
    public function getLowStockAlerts(int $branchId): Collection
    {
        return BranchInventory::with('product')
            ->where('branch_id', $branchId)
            ->whereRaw('quantity_available <= minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->get()
            ->map(function ($inventory) {
                return [
                    'product_name' => $inventory->product->name,
                    'sku' => $inventory->sku,
                    'current_stock' => $inventory->quantity_available,
                    'minimum_stock' => $inventory->minimum_stock,
                    'storage_location' => $inventory->storage_location,
                ];
            });
    }

    /**
     * Sync POS sale to central inventory (for online visibility)
     */
    public function syncToMainInventory(array $saleData): void
    {
        // This method would sync the sale data to the main ecommerce inventory
        // to ensure online stock levels are accurate
        
        foreach ($saleData['items'] as $item) {
            if ($item['type'] === 'regular') {
                // Update main product stock if configured for online visibility
                $branchInventory = BranchInventory::where([
                    'branch_id' => $saleData['branch_id'],
                    'product_id' => $item['id'],
                ])->first();

                if ($branchInventory && $branchInventory->visible_online) {
                    // Trigger product quantity update event for ecommerce
                    event(new \Botble\Ecommerce\Events\ProductQuantityUpdatedEvent(
                        Product::find($item['id'])
                    ));
                }
            }
        }
    }
}