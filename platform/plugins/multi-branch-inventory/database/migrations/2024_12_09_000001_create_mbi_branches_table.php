<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mbi_branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 20)->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('manager_name')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_main_branch')->default(false);
            $table->string('timezone')->default('UTC');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['status', 'is_main_branch']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mbi_branches');
    }
};