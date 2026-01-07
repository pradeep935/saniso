<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quote_form_styles', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->json('setting_value');
            $table->timestamps();
        });
        
        // Insert default style settings
        DB::table('quote_form_styles')->insert([
            [
                'setting_key' => 'form_container',
                'setting_value' => json_encode([
                    'background_color' => '#ffffff',
                    'border_radius' => '8px',
                    'padding' => '30px',
                    'margin' => '20px 0',
                    'box_shadow' => '0 2px 10px rgba(0,0,0,0.1)',
                    'max_width' => '600px'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'form_fields',
                'setting_value' => json_encode([
                    'label_color' => '#333333',
                    'label_font_size' => '14px',
                    'label_font_weight' => '500',
                    'input_border_color' => '#e1e5e9',
                    'input_border_radius' => '4px',
                    'input_padding' => '12px 15px',
                    'input_font_size' => '14px',
                    'input_background' => '#ffffff',
                    'field_margin_bottom' => '20px'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'form_buttons',
                'setting_value' => json_encode([
                    'submit_bg_color' => '#007bff',
                    'submit_text_color' => '#ffffff',
                    'submit_border_radius' => '4px',
                    'submit_padding' => '12px 30px',
                    'submit_font_size' => '16px',
                    'submit_font_weight' => '500',
                    'button_margin_top' => '20px'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'setting_key' => 'responsive_breakpoints',
                'setting_value' => json_encode([
                    'mobile' => '768px',
                    'tablet' => '992px',
                    'desktop' => '1200px',
                    'mobile_padding' => '15px',
                    'tablet_padding' => '25px',
                    'desktop_padding' => '30px'
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_form_styles');
    }
};
