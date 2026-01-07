<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mbi_branch_inventory', function (Blueprint $table) {
            $table->boolean('needs_replenishment')->default(false)->after('notes');
            $table->integer('replenishment_quantity')->nullable()->after('needs_replenishment');
            $table->datetime('replenishment_requested_at')->nullable()->after('replenishment_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('mbi_branch_inventory', function (Blueprint $table) {
            $table->dropColumn(['needs_replenishment', 'replenishment_quantity', 'replenishment_requested_at']);
        });
    }
};
