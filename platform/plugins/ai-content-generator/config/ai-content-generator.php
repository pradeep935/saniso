<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Content Generator Configuration
    |--------------------------------------------------------------------------
    */

    // AI API Settings
    'api_url' => env('AI_API_URL', 'https://api.openai.com/v1'),
    'api_key' => env('AI_API_KEY', ''),
    'model' => env('AI_MODEL', 'gpt-3.5-turbo'),
    
    // Generation Settings
    'max_tokens' => env('AI_MAX_TOKENS', 2000),
    'temperature' => env('AI_TEMPERATURE', 0.7),
    'timeout' => env('AI_TIMEOUT', 30),
    
    // Feature Toggles
    'enable_for_products' => env('AI_ENABLE_PRODUCTS', true),
    'enable_for_blogs' => env('AI_ENABLE_BLOGS', true),
    
    // Rate Limiting
    'rate_limit_per_minute' => env('AI_RATE_LIMIT', 10),
    'rate_limit_per_hour' => env('AI_RATE_LIMIT_HOUR', 100),
    
    // Content Settings
    'default_language' => env('AI_DEFAULT_LANGUAGE', 'en'),
    'content_style' => env('AI_CONTENT_STYLE', 'professional'), // professional, casual, technical
    
    // Content Length Limits - Product Defaults (shorter, punchy content)
    'title_word_limit' => env('AI_TITLE_WORD_LIMIT', 8),
    'description_word_limit' => env('AI_DESCRIPTION_WORD_LIMIT', 30),
    'content_word_limit' => env('AI_CONTENT_WORD_LIMIT', 300),
    'tags_count_limit' => env('AI_TAGS_COUNT_LIMIT', 5),
    'seo_title_limit' => env('AI_SEO_TITLE_LIMIT', 60),
    'seo_description_limit' => env('AI_SEO_DESCRIPTION_LIMIT', 160),
    
    // Blog Content Limits (longer, comprehensive content)
    'blog_title_word_limit' => env('AI_BLOG_TITLE_WORD_LIMIT', 15),
    'blog_description_word_limit' => env('AI_BLOG_DESCRIPTION_WORD_LIMIT', 80),
    'blog_content_word_limit' => env('AI_BLOG_CONTENT_WORD_LIMIT', 1200),
    'blog_tags_count_limit' => env('AI_BLOG_TAGS_COUNT_LIMIT', 8),
    'blog_seo_title_limit' => env('AI_BLOG_SEO_TITLE_LIMIT', 60),
    'blog_seo_description_limit' => env('AI_BLOG_SEO_DESCRIPTION_LIMIT', 160),
];
