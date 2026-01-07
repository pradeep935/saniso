<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_temporary_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('ean')->nullable();
            $table->string('sku')->nullable();
            $table->string('product_code')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->decimal('selling_price', 15, 2);
            $table->string('storage_location')->nullable();
            $table->enum('status', ['active', 'sold_out', 'converted', 'inactive'])->default('active');
            $table->unsignedBigInteger('linked_product_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['ean']);
            $table->index(['sku']);
            $table->index(['product_code']);

            $table->foreign('branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('linked_product_id')->references('id')->on('ec_products')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_temporary_products');
    }
};