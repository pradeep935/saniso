<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ai_content_generator_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
        
        // Insert default settings
        $defaultSettings = [
            'api_key' => '',
            'api_url' => 'https://api.openai.com/v1',
            'model' => 'gpt-3.5-turbo',
            'max_tokens' => '2000',
            'temperature' => '0.7',
            'timeout' => '30',
            'enable_for_products' => '1',
            'enable_for_blogs' => '1',
            'rate_limit_per_minute' => '10',
            'content_style' => 'professional',
            'default_language' => 'en',
            'writing_style' => 'informative',
            'enabled_languages' => '["en","nl","es","fr","de"]',
            'custom_languages' => '[]',
            'product_prompt_prefix' => '',
            'blog_prompt_prefix' => '',
            'auto_populate_tags' => '1',
            'show_word_count' => '1'
        ];
        
        foreach ($defaultSettings as $key => $value) {
            DB::table('ai_content_generator_settings')->insert([
                'key' => $key,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('ai_content_generator_settings');
    }
};
