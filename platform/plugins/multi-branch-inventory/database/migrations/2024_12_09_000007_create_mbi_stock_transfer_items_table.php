<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_stock_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_transfer_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity_requested');
            $table->integer('quantity_shipped')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['stock_transfer_id']);
            $table->index(['product_id']);

            $table->foreign('stock_transfer_id')->references('id')->on('mbi_stock_transfers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_stock_transfer_items');
    }
};