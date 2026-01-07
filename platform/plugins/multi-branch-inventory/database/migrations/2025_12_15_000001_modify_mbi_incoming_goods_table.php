<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mbi_incoming_goods', function (Blueprint $table) {
            if (!Schema::hasColumn('mbi_incoming_goods', 'cmr_image')) {
                $table->string('cmr_image')->nullable()->after('notes');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'packing_slip_image')) {
                $table->string('packing_slip_image')->nullable()->after('cmr_image');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'product_image')) {
                $table->string('product_image')->nullable()->after('packing_slip_image');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'box_barcode')) {
                $table->string('box_barcode')->nullable()->after('product_image');
            }

            // Drop total_value if exists (we only keep total_items)
            if (Schema::hasColumn('mbi_incoming_goods', 'total_value')) {
                $table->dropColumn('total_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mbi_incoming_goods', function (Blueprint $table) {
            if (Schema::hasColumn('mbi_incoming_goods', 'cmr_image')) {
                $table->dropColumn('cmr_image');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'packing_slip_image')) {
                $table->dropColumn('packing_slip_image');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'product_image')) {
                $table->dropColumn('product_image');
            }
            if (Schema::hasColumn('mbi_incoming_goods', 'box_barcode')) {
                $table->dropColumn('box_barcode');
            }

            if (!Schema::hasColumn('mbi_incoming_goods', 'total_value')) {
                $table->decimal('total_value', 15, 2)->default(0)->after('total_items');
            }
        });
    }
};
