<?php

namespace Botble\MultiBranchInventory\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\Ecommerce\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ReplenishmentTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            \Botble\MultiBranchInventory\Providers\MultiBranchInventoryServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Minimal table setup for the test
        Schema::create('mbi_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->timestamps();
        });

        Schema::create('ec_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('mbi_branch_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('maximum_stock')->nullable();
            $table->boolean('needs_replenishment')->default(false);
            $table->integer('replenishment_quantity')->nullable();
            $table->timestamps();
        });
    }

    public function test_trigger_sets_replenishment_flags()
    {
        $branch = \Botble\MultiBranchInventory\Models\Branch::create(['name' => 'T1', 'code' => 'T1']);
        $product = \Botble\Ecommerce\Models\Product::create(['name' => 'Prod', 'sku' => 'P1']);

        $inv = BranchInventory::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity_on_hand' => 5,
            'quantity_available' => 5,
            'minimum_stock' => 10,
            'maximum_stock' => 20,
        ]);

        $this->assertFalse((bool) $inv->needs_replenishment);

        $inv->triggerReplenishment();

        $fresh = BranchInventory::find($inv->id);

        $this->assertTrue((bool) $fresh->needs_replenishment);
        $this->assertEquals(15, $fresh->replenishment_quantity);
        $this->assertNotNull($fresh->replenishment_requested_at);
    }
}
