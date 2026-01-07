<?php

return [
    // Plugin Info
    'name' => 'AI Content Generator',
    'description' => 'Generate content for products and blog posts using AI',
    
    // Main UI
    'ai_generator' => 'AI Content Generator',
    'language' => 'Language',
    'writing_style' => 'Writing Style',
    'generate_content_btn' => 'Generate Content',
    'generating' => 'Generating content...',
    'api_key_required' => 'API Key Required',
    'api_key_required_desc' => 'Please configure your AI API settings to use content generation.',
    'configure_now' => 'Configure Now',
    
    // Product Generation
    'product_prompt' => 'Product Description',
    'product_prompt_placeholder' => 'Enter a brief description of your product...',
    'product_prompt_help' => 'Describe your product, its features, benefits, or target audience.',
    'generate_name' => 'Generate Name',
    'generate_description' => 'Generate Description',
    'generate_content' => 'Generate Content',
    'generate_tags' => 'Generate Tags',
    
    // Blog Generation
    'blog_prompt' => 'Topic or Outline',
    'blog_prompt_placeholder' => 'Enter your blog topic, main ideas, or outline...',
    'blog_prompt_help' => 'Describe your blog topic, key points, or provide an outline.',
    'generate_title' => 'Generate Title',
    
    // Settings Page
    'settings' => [
        'title' => 'AI Content Generator Settings',
        'description' => 'Configure AI service for content generation',
        'api_key' => 'API Key',
        'api_key_placeholder' => 'sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
        'api_key_help' => 'Your OpenAI API key. Get one from OpenAI Platform',
        'api_url' => 'API URL',
        'api_url_help' => 'API endpoint URL',
        'model' => 'AI Model',
        'max_tokens' => 'Max Tokens',
        'max_tokens_help' => 'Maximum tokens per request (100-4000)',
        'temperature' => 'Temperature',
        'temperature_help' => 'Creativity level (0-2)',
        'timeout' => 'Timeout',
        'timeout_help' => 'Request timeout (10-120 seconds)',
        'enable_for_products' => 'Enable for Products',
        'enable_for_products_help' => 'Show AI generator in product edit forms',
        'enable_for_blogs' => 'Enable for Blog Posts',
        'enable_for_blogs_help' => 'Show AI generator in blog post edit forms',
        'rate_limit_per_minute' => 'Rate Limit (per minute)',
        'rate_limit_help' => 'Max requests per minute to prevent API abuse',
        'content_style' => 'Default Content Style',
        'default_language' => 'Default Language',
        'default_language_help' => 'Default language for content generation',
        'default_writing_style' => 'Default Writing Style',
        'enabled_languages' => 'Enabled Languages',
        'enabled_languages_help' => 'Select which languages users can choose from',
        'product_prompt_prefix' => 'Product Prompt Prefix',
        'product_prompt_prefix_help' => 'Default prefix for product generation prompts',
        'blog_prompt_prefix' => 'Blog Prompt Prefix',
        'blog_prompt_prefix_help' => 'Default prefix for blog generation prompts',
        'auto_populate_tags' => 'Auto-populate Tags',
        'auto_populate_tags_help' => 'Automatically suggest tags based on content',
        'show_word_count' => 'Show Word Count',
        'show_word_count_help' => 'Display word count in generation interface',
        'test_connection' => 'Test Connection',
        'save_settings' => 'Save Settings',
        'settings_saved' => 'Settings saved successfully!',
    ],
    
    // Generation Messages
    'generation' => [
        'generate_product_content' => 'Generate Product Content',
        'generate_blog_content' => 'Generate Blog Content',
        'generating' => 'Generating...',
        'success' => 'Content generated successfully!',
        'error' => 'Failed to generate content',
        'error_api_key' => 'API key is not configured',
        'error_rate_limit' => 'Rate limit exceeded. Please try again later.',
        'error_timeout' => 'Request timed out. Please try again.',
        'error_invalid_response' => 'Invalid response from AI service',
        'unavailable' => 'AI service is not configured or available',
        'enter_prompt_first' => 'Please enter a prompt or description first',
        'review_content' => 'AI-generated content should be reviewed and edited as needed',
        'content_generated' => 'Content has been generated and populated in the form fields.',
        'partial_generation' => 'Some content fields were generated successfully.',
    ],
    
    // Language Options
    'languages' => [
        'en' => 'English',
        'es' => 'Spanish (Español)',
        'fr' => 'French (Français)',
        'de' => 'German (Deutsch)',
        'it' => 'Italian (Italiano)',
        'pt' => 'Portuguese (Português)',
        'ru' => 'Russian (Русский)',
        'ja' => 'Japanese (日本語)',
        'ko' => 'Korean (한국어)',
        'zh' => 'Chinese (中文)',
        'ar' => 'Arabic (العربية)',
        'hi' => 'Hindi (हिन्दी)',
    ],
    
    // Writing Styles
    'styles' => [
        'professional' => 'Professional',
        'casual' => 'Casual',
        'technical' => 'Technical',
        'luxury' => 'Luxury',
        'informative' => 'Informative',
        'persuasive' => 'Persuasive',
        'storytelling' => 'Storytelling',
        'conversational' => 'Conversational',
    ],
];
