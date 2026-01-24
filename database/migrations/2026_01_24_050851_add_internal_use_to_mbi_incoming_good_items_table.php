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
        Schema::table('mbi_incoming_good_items', function (Blueprint $table) {
            $table->boolean('internal_use')
            ->default(0)
            ->after('is_new_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mbi_incoming_good_items', function (Blueprint $table) {
            $table->dropColumn('internal_use');
        });
    }
};
