<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mbi_branches', function (Blueprint $table) {
            $table->enum('type', [
                'store',
                'warehouse', 
                'distribution_center',
                'outlet',
                'flagship',
                'pop_up',
                'showroom',
                'kiosk',
                'franchise',
                'online_fulfillment'
            ])->default('store')->after('code');
            
            $table->json('features')->nullable()->after('settings');
        });
    }

    public function down(): void
    {
        Schema::table('mbi_branches', function (Blueprint $table) {
            $table->dropColumn(['type', 'features']);
        });
    }
};