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
        Schema::create('project_quote_form_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label');
            $table->string('type'); // text, email, number, select, checkbox, radio, textarea, file, date, etc.
            $table->json('options')->nullable(); // For select, radio, checkbox options
            $table->string('placeholder')->nullable();
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);
            $table->boolean('enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('validation_rules')->nullable();
            $table->string('css_classes')->nullable();
            $table->json('field_attributes')->nullable(); // min, max, step, etc.
            $table->json('style_config')->nullable(); // Custom styling
            $table->string('default_value')->nullable();
            $table->string('help_text')->nullable();
            $table->string('field_width')->default('col-12'); // Bootstrap column classes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_quote_form_fields');
    }
};