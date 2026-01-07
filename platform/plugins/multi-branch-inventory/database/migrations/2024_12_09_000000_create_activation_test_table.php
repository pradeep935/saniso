<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mbi_plugin_activation')) {
            Schema::create('mbi_plugin_activation', function (Blueprint $table) {
                $table->id();
                $table->timestamp('activated_at')->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_plugin_activation');
    }
};