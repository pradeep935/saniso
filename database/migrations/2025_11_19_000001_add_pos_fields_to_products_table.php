<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->boolean('available_in_pos')->default(false)->after('barcode');
            $table->boolean('pos_only')->default(false)->after('available_in_pos');
        });
    }

    public function down()
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn(['available_in_pos', 'pos_only']);
        });
    }
};
