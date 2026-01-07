<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();
            $table->integer('quantity');
            $table->string('area_size')->nullable();
            $table->string('room_type')->nullable();
            $table->string('installation_needed')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->text('project_description')->nullable();
            $table->json('special_requirements')->nullable();
            $table->boolean('newsletter_subscribe')->default(false);
            $table->enum('status', ['pending', 'in_progress', 'quoted', 'accepted', 'rejected', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->text('quote_details')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->unsignedBigInteger('quoted_by')->nullable();
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('ec_products')->onDelete('cascade');
            $table->foreign('quoted_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'created_at']);
            $table->index(['customer_email']);
            $table->index(['product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
    }
};