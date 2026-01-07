<?php

namespace Botble\AIContentGenerator\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AIContentGeneratorSetting extends Model
{
    protected $table = 'ai_content_generator_settings';
    
    protected $fillable = ['key', 'value'];
    
    public static function ensureTableExists()
    {
        if (!Schema::hasTable('ai_content_generator_settings')) {
            // Create table
            Schema::create('ai_content_generator_settings', function ($table) {
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
    }
    
    public static function get($key, $default = null)
    {
        // Ensure table exists first
        static::ensureTableExists();
        
        $cacheKey = 'ai_content_generator_setting_' . $key;
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }
    
    public static function set($key, $value)
    {
        // Ensure table exists first
        static::ensureTableExists();
        
        $cacheKey = 'ai_content_generator_setting_' . $key;
        Cache::forget($cacheKey);
        
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
    
    public static function getMultiple(array $keys)
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = static::get($key);
        }
        return $results;
    }
    
    public static function setMultiple(array $settings)
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }
}
