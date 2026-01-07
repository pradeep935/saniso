<?php

namespace Botble\AIContentGenerator\Http\Requests;

use Botble\Support\Http\Requests\Request;

class SettingsRequest extends Request
{
    public function rules(): array
    {
        return [
            // API Configuration
            'api_key' => 'nullable|string|max:255',
            'api_url' => 'nullable|url|max:255',
            'model' => 'nullable|string|max:100',
            
            // Generation Settings
            'max_tokens' => 'nullable|integer|min:100|max:4000',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'timeout' => 'nullable|integer|min:10|max:120',
            
            // Feature Settings
            'enable_for_products' => 'nullable|boolean',
            'enable_for_blogs' => 'nullable|boolean',
            'rate_limit_per_minute' => 'nullable|integer|min:1|max:100',
            'content_style' => 'nullable|string|max:50',
            
            // Language Settings
            'default_language' => 'nullable|string|max:10',
            'writing_style' => 'nullable|string|max:50',
            'enabled_languages' => 'nullable',
            'custom_languages' => 'nullable|string',
            
            // Content Customization
            'product_prompt_prefix' => 'nullable|string|max:1000',
            'blog_prompt_prefix' => 'nullable|string|max:1000',
            'auto_populate_tags' => 'nullable|boolean',
            'show_word_count' => 'nullable|boolean',
        ];
    }

    public function attributes(): array
    {
        return [
            // API Configuration
            'api_key' => 'API Key',
            'api_url' => 'API URL',
            'model' => 'AI Model',
            
            // Generation Settings
            'max_tokens' => 'Max Tokens',
            'temperature' => 'Temperature',
            'timeout' => 'Timeout',
            
            // Feature Settings
            'enable_for_products' => 'Enable for Products',
            'enable_for_blogs' => 'Enable for Blogs',
            'rate_limit_per_minute' => 'Rate Limit Per Minute',
            'content_style' => 'Default Content Style',
            
            // Language Settings
            'default_language' => 'Default Language',
            'writing_style' => 'Default Writing Style',
            'enabled_languages' => 'Enabled Languages',
            
            // Content Customization
            'product_prompt_prefix' => 'Product Prompt Prefix',
            'blog_prompt_prefix' => 'Blog Prompt Prefix',
            'auto_populate_tags' => 'Auto-populate Tags',
            'show_word_count' => 'Show Word Count',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Handle enabled_languages array
        if ($this->has('enabled_languages') && is_array($this->enabled_languages)) {
            $this->merge([
                'enabled_languages' => json_encode($this->enabled_languages)
            ]);
        }
        
        // Handle custom_languages (already JSON string from frontend)
        if ($this->has('custom_languages') && is_string($this->custom_languages)) {
            // Validate JSON format
            $customLangs = json_decode($this->custom_languages, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->merge(['custom_languages' => '{}']);
            }
        }
        
        // Convert checkboxes to boolean
        $booleanFields = [
            'enable_for_products',
            'enable_for_blogs', 
            'auto_populate_tags',
            'show_word_count'
        ];
        
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => (bool) $this->input($field)
                ]);
            } else {
                $this->merge([
                    $field => false
                ]);
            }
        }
    }
}
