<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_incoming_goods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->string('supplier_name');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->datetime('receiving_date');
            $table->string('reference_number')->unique();
            $table->enum('status', ['draft', 'received', 'processed'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->integer('total_items')->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status']);
            $table->index(['receiving_date']);
            $table->index(['reference_number']);

            $table->foreign('branch_id')->references('id')->on('mbi_branches')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_incoming_goods');
    }
};