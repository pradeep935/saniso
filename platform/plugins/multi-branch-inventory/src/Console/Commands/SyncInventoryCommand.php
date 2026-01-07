<?php

namespace Botble\MultiBranchInventory\Console\Commands;

use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncInventoryCommand extends Command
{
    protected $signature = 'multi-branch-inventory:sync {--branch-id=} {--product-id=}';

    protected $description = 'Sync main product inventory with branch inventories';

    public function handle(): int
    {
        $this->info('Starting inventory synchronization...');

        $branchId = $this->option('branch-id');
        $productId = $this->option('product-id');

        $query = BranchInventory::with(['product', 'branch'])
            ->where('visible_online', true)
            ->where('only_visible_in_pos', false);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        $inventories = $query->get();

        $this->info("Processing {$inventories->count()} inventory items...");

        $progressBar = $this->output->createProgressBar($inventories->count());

        foreach ($inventories as $inventory) {
            try {
                // Calculate total available quantity across all branches for this product
                $totalQuantity = BranchInventory::where('product_id', $inventory->product_id)
                    ->where('visible_online', true)
                    ->where('only_visible_in_pos', false)
                    ->sum('quantity_available');

                // Update main product quantity
                DB::table('ec_products')
                    ->where('id', $inventory->product_id)
                    ->update([
                        'quantity' => $totalQuantity,
                        'stock_status' => $totalQuantity > 0 ? 'in_stock' : 'out_of_stock',
                        'updated_at' => now(),
                    ]);

                $progressBar->advance();

            } catch (\Exception $e) {
                $this->error("Error syncing product {$inventory->product_id}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('Inventory synchronization completed!');

        return 0;
    }
}