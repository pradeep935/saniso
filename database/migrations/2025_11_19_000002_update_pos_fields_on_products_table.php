<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ec_products', function (Blueprint $table) {
            if (Schema::hasColumn('ec_products', 'pos_only')) {
                $table->dropColumn('pos_only');
            }
            if (!Schema::hasColumn('ec_products', 'available_in_webshop')) {
                $table->boolean('available_in_webshop')->default(false)->after('available_in_pos');
            }
        });
    }

    public function down()
    {
        Schema::table('ec_products', function (Blueprint $table) {
            if (Schema::hasColumn('ec_products', 'available_in_webshop')) {
                $table->dropColumn('available_in_webshop');
            }
            if (!Schema::hasColumn('ec_products', 'pos_only')) {
                $table->boolean('pos_only')->default(false)->after('available_in_pos');
            }
        });
    }
};
