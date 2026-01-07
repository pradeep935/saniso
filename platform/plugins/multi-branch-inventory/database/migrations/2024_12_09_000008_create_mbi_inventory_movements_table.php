<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_inventory_id')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->enum('type', [
                'add',
                'subtract',
                'set',
                'transfer_in',
                'transfer_out',
                'sale',
                'return',
                'damage',
                'count',
                'incoming'
            ]);
            $table->integer('quantity_before')->default(0);
            $table->integer('quantity_changed');
            $table->integer('quantity_after')->default(0);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_inventory_id']);
            $table->index(['branch_id', 'product_id']);
            $table->index(['type']);
            $table->index(['created_at']);
            $table->index(['reference_id', 'reference_type']);

            $table->foreign('branch_inventory_id')->references('id')->on('mbi_branch_inventory')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_inventory_movements');
    }
};