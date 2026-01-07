<?php

namespace Botble\MultiBranchInventory\Listeners;

use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Events\ProductQuantityUpdatedEvent;

class SyncProductQuantityListener
{
    /**
     * Handle product quantity update from main ecommerce system
     */
    public function handle(ProductQuantityUpdatedEvent $event): void
    {
        $product = $event->product;

        // Update the main branch inventory when product quantity changes
        $mainBranch = \Botble\MultiBranchInventory\Models\Branch::where('is_main_branch', true)->first();

        if ($mainBranch) {
            $branchInventory = BranchInventory::firstOrCreate([
                'branch_id' => $mainBranch->id,
                'product_id' => $product->id,
            ], [
                'sku' => $product->sku,
                'ean' => $product->barcode,
                'quantity_on_hand' => 0,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'visible_online' => true,
                'visible_in_pos' => true,
            ]);

            // Only sync if this is the main source of truth
            $productQuantity = $product->quantity ?? 0;
            if ($branchInventory->quantity_on_hand !== $productQuantity) {
                $branchInventory->updateStock(
                    $productQuantity,
                    'set',
                    'Synced from main product inventory'
                );
            }
        }
    }
}