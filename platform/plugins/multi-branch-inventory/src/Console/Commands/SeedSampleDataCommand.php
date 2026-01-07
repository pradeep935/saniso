<?php

namespace Botble\MultiBranchInventory\Console\Commands;

use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\Ecommerce\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SeedSampleDataCommand extends Command
{
    protected $signature = 'multi-branch-inventory:seed-sample';
    protected $description = 'Seed sample data for Multi-Branch Inventory';

    public function handle()
    {
        $this->info('🌱 Seeding Multi-Branch Inventory Sample Data...');

        // Check if tables exist
        $tables = [
            'mbi_branches',
            'mbi_branch_inventory', 
            'mbi_incoming_goods',
            'mbi_stock_transfers'
        ];

        $this->info('📋 Checking tables...');
        foreach ($tables as $table) {
            $exists = Schema::hasTable($table);
            $this->line("  {$table}: " . ($exists ? '✅' : '❌'));
        }

        if (!Schema::hasTable('mbi_branches')) {
            $this->error('❌ Tables do not exist! Please run migrations first.');
            return 1;
        }

        // Create sample branches
        $this->info('🏢 Creating sample branches...');
        
        $branches = [
            [
                'name' => 'Main Store',
                'code' => 'MAIN',
                'address' => '123 Main Street, Downtown',
                'phone' => '555-0123',
                'email' => 'main@store.com',
                'manager_name' => 'John Smith',
                'is_active' => true,
            ],
            [
                'name' => 'North Branch',
                'code' => 'NORTH',
                'address' => '456 North Avenue',
                'phone' => '555-0456',
                'email' => 'north@store.com',
                'manager_name' => 'Jane Doe',
                'is_active' => true,
            ],
            [
                'name' => 'South Branch',
                'code' => 'SOUTH',
                'address' => '789 South Boulevard',
                'phone' => '555-0789',
                'email' => 'south@store.com',
                'manager_name' => 'Mike Johnson',
                'is_active' => true,
            ]
        ];

        foreach ($branches as $branchData) {
            $branch = Branch::firstOrCreate(
                ['code' => $branchData['code']],
                $branchData
            );
            $this->line("  Created/Found: {$branch->name} (ID: {$branch->id})");
        }

        // Create sample inventory for existing products
        $this->info('📦 Creating sample inventory...');
        $products = Product::take(5)->get();
        $branches = Branch::all();

        if ($products->count() > 0 && $branches->count() > 0) {
            foreach ($branches as $branch) {
                foreach ($products as $product) {
                    $inventory = BranchInventory::firstOrCreate([
                        'branch_id' => $branch->id,
                        'product_id' => $product->id,
                    ], [
                        'quantity' => rand(10, 100),
                        'min_quantity' => 5,
                        'max_quantity' => 200,
                        'location' => 'Aisle ' . rand(1, 10) . ', Shelf ' . chr(65 + rand(0, 5)),
                    ]);
                    
                    $this->line("  {$branch->name}: {$product->name} - Qty: {$inventory->quantity}");
                }
            }
        } else {
            $this->warn('⚠️  No products found. Please create some products first.');
        }

        $this->info('✅ Sample data seeding completed!');
        $this->line('');
        $this->line('🎯 You can now:');
        $this->line('  1. Visit Admin Panel → Multi-Branch Inventory');
        $this->line('  2. View branches, inventory, and create transfers');
        $this->line('  3. Test all the functionality');

        return 0;
    }
}