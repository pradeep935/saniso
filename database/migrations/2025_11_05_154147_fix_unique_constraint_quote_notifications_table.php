<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing unique constraint
        Schema::table('quote_notifications', function (Blueprint $table) {
            $table->dropUnique('unique_quote_notification');
        });
        
        // Add a hash column for better duplicate detection
        Schema::table('quote_notifications', function (Blueprint $table) {
            $table->string('notification_hash', 64)->nullable()->after('data');
            $table->unique('notification_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_notifications', function (Blueprint $table) {
            $table->dropUnique(['notification_hash']);
            $table->dropColumn('notification_hash');
        });
        
        // Restore the original constraint
        Schema::table('quote_notifications', function (Blueprint $table) {
            $table->unique([
                'quote_request_id',
                'type',
                'recipient_type',
                'recipient_id',
                'title'
            ], 'unique_quote_notification');
        });
    }
};
