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
        Schema::table('quote_notifications', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate notifications
            $table->unique([
                'quote_request_id',
                'type',
                'recipient_type',
                'recipient_id',
                'title'
            ], 'unique_quote_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_notifications', function (Blueprint $table) {
            $table->dropUnique('unique_quote_notification');
        });
    }
};
