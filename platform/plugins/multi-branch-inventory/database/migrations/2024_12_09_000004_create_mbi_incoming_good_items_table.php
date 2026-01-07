<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_incoming_good_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('incoming_good_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('ean')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_name');
            $table->integer('quantity_expected')->default(0);
            $table->integer('quantity_received')->default(0);
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->string('storage_location')->nullable();
            $table->text('condition_notes')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_new_product')->default(false);
            $table->timestamps();

            $table->index(['incoming_good_id']);
            $table->index(['product_id']);
            $table->index(['ean']);
            $table->index(['sku']);

            $table->foreign('incoming_good_id')->references('id')->on('mbi_incoming_goods')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_incoming_good_items');
    }
};