<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_pickup_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('quantity');
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_email')->nullable();
            $table->date('pickup_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['reserved', 'picked_up', 'cancelled', 'expired'])->default('reserved');
            $table->string('reservation_number')->unique();
            $table->datetime('expires_at');
            $table->datetime('picked_up_at')->nullable();
            $table->unsignedBigInteger('picked_up_by')->nullable();
            $table->datetime('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['product_id']);
            $table->index(['reservation_number']);
            $table->index(['customer_phone']);
            $table->index(['expires_at']);
            $table->index(['pickup_date']);

            $table->foreign('branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
            $table->foreign('picked_up_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_pickup_reservations');
    }
};