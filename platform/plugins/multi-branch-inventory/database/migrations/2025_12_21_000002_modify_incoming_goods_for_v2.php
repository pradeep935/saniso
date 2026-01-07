<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mbi_incoming_goods', function (Blueprint $table) {
            if (!Schema::hasColumn('mbi_incoming_goods', 'cmr_images')) {
                $table->json('cmr_images')->nullable()->after('cmr_image');
            }
            if (!Schema::hasColumn('mbi_incoming_goods', 'packing_slip_images')) {
                $table->json('packing_slip_images')->nullable()->after('cmr_images');
            }
            if (!Schema::hasColumn('mbi_incoming_goods', 'delivery_images')) {
                $table->json('delivery_images')->nullable()->after('packing_slip_images');
            }
            if (!Schema::hasColumn('mbi_incoming_goods', 'proforma_images')) {
                $table->json('proforma_images')->nullable()->after('delivery_images');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'for_internal_use')) {
                $table->boolean('for_internal_use')->default(false)->after('status');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'order_date')) {
                $table->datetime('order_date')->nullable()->after('receiving_date');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'order_reference')) {
                $table->string('order_reference')->nullable()->after('order_date');
            }
        });

        // Add Backorder to status enum (MySQL)
        try {
            DB::statement("ALTER TABLE `mbi_incoming_goods` MODIFY COLUMN `status` ENUM('draft','received','processed','backorder') NOT NULL DEFAULT 'draft';");
        } catch (\Exception $e) {
            // ignore - some DB engines may not support this statement in the environment
        }
    }

    public function down(): void
    {
        Schema::table('mbi_incoming_goods', function (Blueprint $table) {
            if (Schema::hasColumn('mbi_incoming_goods', 'cmr_images')) {
                $table->dropColumn('cmr_images');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'packing_slip_images')) {
                $table->dropColumn('packing_slip_images');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'delivery_images')) {
                $table->dropColumn('delivery_images');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'proforma_images')) {
                $table->dropColumn('proforma_images');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'for_internal_use')) {
                $table->dropColumn('for_internal_use');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'order_date')) {
                $table->dropColumn('order_date');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'order_reference')) {
                $table->dropColumn('order_reference');
            }
        });

        try {
            DB::statement("ALTER TABLE `mbi_incoming_goods` MODIFY COLUMN `status` ENUM('draft','received','processed') NOT NULL DEFAULT 'draft';");
        } catch (\Exception $e) {
            // ignore
        }
    }
};
