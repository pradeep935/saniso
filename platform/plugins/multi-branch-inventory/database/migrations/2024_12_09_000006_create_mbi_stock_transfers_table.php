<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('from_branch_id');
            $table->unsignedBigInteger('to_branch_id');
            $table->string('reference_number')->unique();
            $table->enum('status', [
                'pending',
                'approved', 
                'picking',
                'in_transit',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('picked_by')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->datetime('requested_at')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->datetime('picked_at')->nullable();
            $table->datetime('shipped_at')->nullable();
            $table->datetime('received_at')->nullable();
            $table->text('notes')->nullable();
            $table->integer('total_items')->default(0);
            $table->string('tracking_number')->nullable();
            $table->string('shipping_method')->nullable();
            $table->timestamps();

            $table->index(['from_branch_id', 'status']);
            $table->index(['to_branch_id', 'status']);
            $table->index(['reference_number']);
            $table->index(['status']);

            $table->foreign('from_branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('to_branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('picked_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_stock_transfers');
    }
};