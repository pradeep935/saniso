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
        Schema::create('project_quote_requests', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->string('customer_company')->nullable();
            $table->text('project_description');
            $table->string('project_type')->nullable();
            $table->string('budget_range')->nullable();
            $table->string('timeline')->nullable();
            $table->string('area_size')->nullable();
            $table->string('installation_needed')->nullable();
            $table->json('special_requirements')->nullable();
            $table->json('form_data')->nullable(); // Store dynamic form field data
            $table->json('uploaded_files')->nullable();
            $table->boolean('newsletter_subscribe')->default(false);
            $table->enum('status', ['pending', 'in_progress', 'quoted', 'accepted', 'rejected', 'completed'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->decimal('quoted_price', 10, 2)->nullable();
            $table->text('quote_details')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->unsignedBigInteger('quoted_by')->nullable();
            $table->timestamps();

            $table->foreign('quoted_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['status', 'created_at']);
            $table->index(['customer_email']);
            $table->index(['project_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_quote_requests');
    }
};