<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_branch_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->string('sku')->nullable();
            $table->string('ean')->nullable();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->integer('maximum_stock')->nullable();
            $table->string('storage_location')->nullable();
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->decimal('local_price', 15, 2)->nullable();
            $table->decimal('promo_price', 15, 2)->nullable();
            $table->datetime('promo_start_date')->nullable();
            $table->datetime('promo_end_date')->nullable();
            $table->boolean('visible_online')->default(true);
            $table->boolean('visible_in_pos')->default(true);
            $table->boolean('only_visible_in_pos')->default(false);
            $table->datetime('last_counted_at')->nullable();
            $table->datetime('last_restocked_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'product_id']);
            $table->index(['branch_id', 'visible_online']);
            $table->index(['branch_id', 'visible_in_pos']);
            $table->index(['quantity_available']);
            $table->index(['sku']);
            $table->index(['ean']);

            $table->foreign('branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_branch_inventory');
    }
};