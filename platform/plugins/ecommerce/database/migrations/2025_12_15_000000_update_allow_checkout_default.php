<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration sets the default for `allow_checkout_when_out_of_stock` to 1
     * and updates existing rows to enable it.
     */
    public function up(): void
    {
        // Modify column default (MySQL)
        DB::statement("ALTER TABLE `ec_products` MODIFY `allow_checkout_when_out_of_stock` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;");

        // Update existing rows to enabled
        DB::statement("UPDATE `ec_products` SET `allow_checkout_when_out_of_stock` = 1 WHERE `allow_checkout_when_out_of_stock` IS NULL OR `allow_checkout_when_out_of_stock` = 0;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert default to 0
        DB::statement("ALTER TABLE `ec_products` MODIFY `allow_checkout_when_out_of_stock` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0;");
    }
};
