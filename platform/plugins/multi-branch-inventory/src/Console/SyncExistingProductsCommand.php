<?php

namespace Botble\MultiBranchInventory\Console;

use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Console\Command;

class SyncExistingProductsCommand extends Command
{
    protected $signature = 'multi-branch-inventory:sync-existing-products';
    
    protected $description = 'Sync existing products (created before plugin) to main branch with their current quantities';

    public function handle(): void
    {
        $this->info('Syncing existing products to main branch...');
        
        // Get main branch
        $mainBranch = Branch::where('is_main_branch', true)->first();
        if (!$mainBranch) {
            $mainBranch = Branch::where('status', 'active')->first();
        }
        
        if (!$mainBranch) {
            $this->error('No active branch found. Please create a branch first.');
            return;
        }
        
        $this->info("Using main branch: {$mainBranch->name}");

        // Get products that don't have any branch inventory
        $productsWithoutBranch = Product::where('status', 'published')
            ->whereDoesntHave('branchInventories')
            ->get();

        $this->info("Found {$productsWithoutBranch->count()} products without branch inventory");
        
        if ($productsWithoutBranch->isEmpty()) {
            $this->info('All products are already synced to branches.');
            return;
        }

        $bar = $this->output->createProgressBar($productsWithoutBranch->count());
        $bar->start();

        $syncedCount = 0;
        foreach ($productsWithoutBranch as $product) {
            // Create branch inventory with the product's current quantity
            BranchInventory::create([
                'product_id' => $product->id,
                'branch_id' => $mainBranch->id,
                'sku' => $product->sku,
                'quantity_on_hand' => $product->quantity ?: 0,
                'quantity_available' => $product->quantity ?: 0,
                'quantity_reserved' => 0,
                'minimum_stock' => 0,
                'cost_price' => $product->cost_per_item ?? 0,
                'selling_price' => $product->price ?? 0,
                'visible_online' => true,
                'visible_in_pos' => true,
                'only_visible_in_pos' => false,
            ]);
            
            // Enable storehouse management for the product
            $product->update([
                'with_storehouse_management' => true
            ]);
            
            $syncedCount++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        
        $this->info("Successfully synced {$syncedCount} products to main branch: {$mainBranch->name}");
        $this->info('All products now have branch inventory records!');
    }
}