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
        Schema::table('quote_settings', function (Blueprint $table) {
            $table->boolean('enable_tax_on_quotes')->default(true)->after('require_login');
            $table->boolean('quote_prices_include_tax')->default(false)->after('enable_tax_on_quotes');
            $table->string('quote_tax_calculation')->default('auto')->after('quote_prices_include_tax'); // auto, manual, none
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_settings', function (Blueprint $table) {
            $table->dropColumn(['enable_tax_on_quotes', 'quote_prices_include_tax', 'quote_tax_calculation']);
        });
    }
};
