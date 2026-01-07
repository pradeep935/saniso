<?php

namespace Botble\MultiBranchInventory\Listeners;

use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Events\OrderCreated;

class HandleEcommerceOrderListener
{
    /**
     * Handle ecommerce order creation
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Check if order has branch selection for pickup
        $pickupBranchId = $order->pickup_branch_id ?? null;

        if (!$pickupBranchId) {
            // If no specific branch, use main branch or first available
            $pickupBranchId = \Botble\MultiBranchInventory\Models\Branch::where('is_main_branch', true)
                ->orWhere('status', 'active')
                ->first()?->id;
        }

        if ($pickupBranchId) {
            foreach ($order->products as $orderProduct) {
                $branchInventory = BranchInventory::where([
                    'branch_id' => $pickupBranchId,
                    'product_id' => $orderProduct->id,
                ])->first();

                if ($branchInventory) {
                    // Reserve stock for the order
                    $quantity = $orderProduct->pivot->qty;
                    
                    if ($branchInventory->reserveStock($quantity)) {
                        // Log the reservation
                        \Botble\MultiBranchInventory\Models\InventoryMovement::create([
                            'branch_inventory_id' => $branchInventory->id,
                            'branch_id' => $pickupBranchId,
                            'product_id' => $orderProduct->id,
                            'type' => 'reserve',
                            'quantity_changed' => -$quantity,
                            'reason' => "Order reservation: {$order->code}",
                            'reference_id' => $order->id,
                            'reference_type' => get_class($order),
                        ]);
                    }
                }
            }
        }
    }
}