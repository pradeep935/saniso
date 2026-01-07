<?php

namespace Botble\MultiBranchInventory\Console;

use Botble\Ecommerce\Models\Product;
use Illuminate\Console\Command;

class EnableStorehouseManagementCommand extends Command
{
    protected $signature = 'multi-branch-inventory:enable-storehouse-management';
    
    protected $description = 'Enable storehouse management for all existing products';

    public function handle(): void
    {
        $this->info('Enabling storehouse management for all products...');
        
        $count = Product::where('with_storehouse_management', '!=', true)
            ->orWhereNull('with_storehouse_management')
            ->update(['with_storehouse_management' => true]);
        
        $this->info("Enabled storehouse management for {$count} products.");
        
        // Also sync quantities for all products (chunked for performance)
        $this->info('Syncing quantities from branch inventories...');
        
        $totalSynced = 0;
        $chunkSize = 500;
        
        Product::whereHas('branchInventories')
            ->with('branchInventories')
            ->chunk($chunkSize, function ($products) use (&$totalSynced) {
                foreach ($products as $product) {
                    $globalQuantity = $product->branchInventories->sum('quantity_available');
                    $product->update(['quantity' => $globalQuantity]);
                    $totalSynced++;
                    
                    if ($totalSynced % 100 === 0) {
                        $this->info("Synced {$totalSynced} products...");
                    }
                }
            });
        
        $this->info("Synced quantities for {$totalSynced} products with branch inventories.");
        $this->info('Done!');
    }
}