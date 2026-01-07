<?php

namespace Botble\MultiBranchInventory\Console\Commands;

use Illuminate\Console\Command;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;

class SyncProductQuantitiesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'multi-branch-inventory:sync-quantities {--product-id= : Sync specific product ID}';

    /**
     * The console command description.
     */
    protected $description = 'Sync ecommerce product quantities with branch inventory totals';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!class_exists(Product::class)) {
            $this->error('Ecommerce plugin is not installed or activated.');
            return 1;
        }

        $productId = $this->option('product-id');

        if ($productId) {
            $this->syncSingleProduct($productId);
        } else {
            $this->syncAllProducts();
        }

        return 0;
    }

    protected function syncSingleProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            $this->error("Product with ID {$productId} not found.");
            return;
        }

        $totalQuantity = BranchInventory::where('product_id', $productId)
            ->sum('quantity_available');

        $oldQuantity = $product->quantity;
        $product->update(['quantity' => $totalQuantity]);

        $this->info("Product '{$product->name}' quantity synced: {$oldQuantity} → {$totalQuantity}");
    }

    protected function syncAllProducts()
    {
        $this->info('Starting bulk quantity sync...');

        $productsWithInventory = BranchInventory::select('product_id')
            ->groupBy('product_id')
            ->pluck('product_id');

        $bar = $this->output->createProgressBar($productsWithInventory->count());
        $bar->start();

        $syncedCount = 0;
        $totalUpdated = 0;

        foreach ($productsWithInventory as $productId) {
            $product = Product::find($productId);
            if (!$product) {
                continue;
            }

            $totalQuantity = BranchInventory::where('product_id', $productId)
                ->sum('quantity_available');

            $oldQuantity = $product->quantity;
            
            if ($oldQuantity != $totalQuantity) {
                $product->update(['quantity' => $totalQuantity]);
                $totalUpdated++;
            }
            
            $syncedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $this->info("Sync completed!");
        $this->info("Products processed: {$syncedCount}");
        $this->info("Products updated: {$totalUpdated}");

        // Also sync products that have no branch inventory (set to 0)
        $productsWithoutInventory = Product::whereNotIn('id', $productsWithInventory)->where('quantity', '>', 0);
        
        if ($productsWithoutInventory->count() > 0) {
            $this->info("Found " . $productsWithoutInventory->count() . " products without branch inventory. Setting quantities to 0...");
            
            $productsWithoutInventory->update(['quantity' => 0]);
            $this->info("Updated products without branch inventory.");
        }
    }
}