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
        Schema::create('quote_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained('quote_requests')->onDelete('cascade');
            $table->string('type'); // 'status_change', 'new_message', 'quote_created', 'quote_accepted', 'quote_rejected'
            $table->string('recipient_type'); // 'customer', 'vendor', 'admin'
            $table->unsignedBigInteger('recipient_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            $table->index(['recipient_type', 'recipient_id', 'is_read']);
            $table->index(['quote_request_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_notifications');
    }
};
