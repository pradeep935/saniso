<?php

namespace Botble\AIContentGenerator\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class AIContentService
{
    protected string $apiUrl;
    protected ?string $apiKey;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = ai_setting('api_url', 'https://api.openai.com/v1');
        
        // Handle potential null values from settings
        $apiKey = ai_setting('api_key');
        $this->apiKey = is_string($apiKey) && !empty($apiKey) ? $apiKey : null;
        
        $this->timeout = (int) ai_setting('api_timeout', 30);
    }

    /**
     * Check if AI service is available and properly configured
     */
    public function isAvailable(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Check rate limits before making API calls
     */
    protected function checkRateLimit(): bool
    {
        $minuteLimit = (int) ai_setting('rate_limit_per_minute', 10);
        $hourLimit = (int) ai_setting('rate_limit_per_hour', 100);
        $dayLimit = (int) ai_setting('rate_limit_per_day', 500);
        
        // Simple cache-based rate limiting
        $cacheKey = 'ai_requests';
        $now = now();
        
        // Get current usage
        $usage = cache()->get($cacheKey, [
            'minute' => ['count' => 0, 'reset' => $now->addMinute()],
            'hour' => ['count' => 0, 'reset' => $now->addHour()],
            'day' => ['count' => 0, 'reset' => $now->addDay()],
        ]);
        
        // Reset counters if time has passed
        if ($now->greaterThan($usage['minute']['reset'])) {
            $usage['minute'] = ['count' => 0, 'reset' => $now->copy()->addMinute()];
        }
        if ($now->greaterThan($usage['hour']['reset'])) {
            $usage['hour'] = ['count' => 0, 'reset' => $now->copy()->addHour()];
        }
        if ($now->greaterThan($usage['day']['reset'])) {
            $usage['day'] = ['count' => 0, 'reset' => $now->copy()->addDay()];
        }
        
        // Check limits
        if ($usage['minute']['count'] >= $minuteLimit ||
            $usage['hour']['count'] >= $hourLimit ||
            $usage['day']['count'] >= $dayLimit) {
            return false;
        }
        
        // Increment counters
        $usage['minute']['count']++;
        $usage['hour']['count']++;
        $usage['day']['count']++;
        
        // Save usage
        cache()->put($cacheKey, $usage, $usage['day']['reset']);
        
        return true;
    }

    /**
     * Generate product content based on title and description
     */
    public function generateProductContent(string $title, string $description = '', array $options = []): array
    {
        $prompt = $this->buildProductPrompt($title, $description, $options);
        
        return $this->generateContent($prompt, 'product');
    }

    /**
     * Generate blog post content based on title and description
     */
    public function generateBlogContent(string $title, string $description = '', array $options = []): array
    {
        $prompt = $this->buildBlogPrompt($title, $description, $options);
        
        return $this->generateContent($prompt, 'blog');
    }

    /**
     * Translate content to target language
     */
    public function translateContent(array $sourceContent, string $sourceLanguage, string $targetLanguage, array $options = []): array
    {
        $contentString = json_encode($sourceContent);
        return $this->generateTranslatedContent($contentString, $targetLanguage, 'translation', array_merge($options, [
            'source_language' => $sourceLanguage
        ]));
    }

    /**
     * Generate translated content based on existing content
     */
    public function generateTranslatedContent(string $sourceContent, string $targetLanguage, string $type, array $options = []): array
    {
        $prompt = $this->buildTranslationPrompt($sourceContent, $targetLanguage, $type, $options);
        
        return $this->generateContent($prompt, 'translation');
    }

    /**
     * Build prompt for content translation
     */
    protected function buildTranslationPrompt(string $sourceContent, string $targetLanguage, string $type, array $options): string
    {
        // Get word limits based on content type
        if ($type === 'blog') {
            $titleWordLimit = (int) ai_setting('blog_title_word_limit', 15);
            $descriptionWordLimit = (int) ai_setting('blog_description_word_limit', 80);
            $contentWordLimit = (int) ai_setting('blog_content_word_limit', 1200);
            $tagsCount = (int) ai_setting('blog_tags_count_limit', 8);
            $seoTitleLimit = (int) ai_setting('blog_seo_title_limit', 60);
            $seoDescriptionLimit = (int) ai_setting('blog_seo_description_limit', 160);
        } else {
            $titleWordLimit = (int) ai_setting('title_word_limit', 8);
            $descriptionWordLimit = (int) ai_setting('description_word_limit', 30);
            $contentWordLimit = (int) ai_setting('content_word_limit', 300);
            $tagsCount = (int) ai_setting('tags_count_limit', 5);
            $seoTitleLimit = (int) ai_setting('seo_title_limit', 60);
            $seoDescriptionLimit = (int) ai_setting('seo_description_limit', 160);
        }

        $languageNames = [
            'en' => 'English',
            'nl' => 'Dutch (Nederlands)',
            'fr' => 'French (Français)',
            'de' => 'German (Deutsch)',
            'es' => 'Spanish (Español)',
            'it' => 'Italian (Italiano)',
            'pt' => 'Portuguese (Português)',
            'ru' => 'Russian (Русский)',
            'ja' => 'Japanese (日本語)',
            'ko' => 'Korean (한국어)',
            'zh' => 'Chinese (中文)',
            'ar' => 'Arabic (العربية)',
        ];

        $targetLanguageName = $languageNames[$targetLanguage] ?? $targetLanguage;
        
        // Check if this is a direct translation (exact translation without enhancements)
        $tone = $options['tone'] ?? 'professional';
        $isDirectTranslation = $tone === 'direct' || $tone === 'exact';
        
        // Map content type to human-readable name
        $contentTypeNames = [
            'blog' => 'blog post',
            'product' => 'product'
        ];
        $contentTypeName = $contentTypeNames[$type] ?? $type;

        if ($isDirectTranslation) {
            // Direct translation prompt - translate exactly without additions
            $prompt = "Translate the following {$contentTypeName} content to {$targetLanguageName} EXACTLY as provided:\n\n";
            $prompt .= "SOURCE CONTENT:\n{$sourceContent}\n\n";
            
            $prompt .= "DIRECT TRANSLATION REQUIREMENTS:\n";
            $prompt .= "- Translate EXACTLY what is provided, nothing more, nothing less\n";
            $prompt .= "- Do NOT add any extra content, descriptions, or enhancements\n";
            $prompt .= "- Do NOT expand on the original content\n";
            $prompt .= "- If a field is empty in the source, leave it empty in translation\n";
            $prompt .= "- Keep the exact same meaning and structure\n";
            $prompt .= "- Use natural {$targetLanguageName} but stay true to the original length and content\n";
            $prompt .= "- Preserve technical terms and product names appropriately\n\n";
        } else {
            // Enhanced translation prompt (original behavior)
            $prompt = "Translate the following {$contentTypeName} content to {$targetLanguageName}:\n\n";
            $prompt .= "SOURCE CONTENT:\n{$sourceContent}\n\n";
        }
        
        if (!$isDirectTranslation) {
            $prompt .= "TRANSLATION REQUIREMENTS:\n";
            $prompt .= "- Maintain the original meaning and tone while adapting to {$targetLanguageName} cultural context\n";
            $prompt .= "- Keep the same structure and formatting (HTML tags, lists, headings)\n";
            $prompt .= "- Adapt cultural references, idioms, and expressions to be natural in {$targetLanguageName}\n";
            $prompt .= "- Maintain SEO-friendly language while being natural and human-like\n";
            $prompt .= "- Preserve technical terms but make them accessible to {$targetLanguageName} speakers\n";
            $prompt .= "- Keep the same emotional tone and persuasive elements\n";
            $prompt .= "- Ensure translations sound native, not machine-translated\n\n";

            if ($type === 'blog') {
                $prompt .= "BLOG TRANSLATION SPECIFICS:\n";
                $prompt .= "- Adapt storytelling elements to resonate with {$targetLanguageName} culture\n";
                $prompt .= "- Maintain engaging, conversational tone appropriate for {$targetLanguageName} readers\n";
                $prompt .= "- Keep educational value and actionable advice clear and practical\n";
                $prompt .= "- Adapt examples and case studies to be relevant to {$targetLanguageName} audience\n\n";
            } else {
                $prompt .= "PRODUCT TRANSLATION SPECIFICS:\n";
                $prompt .= "- Adapt product benefits to appeal to {$targetLanguageName} market preferences\n";
                $prompt .= "- Use persuasive language natural to {$targetLanguageName} e-commerce\n";
                $prompt .= "- Keep product features clear and compelling for {$targetLanguageName} customers\n\n";
            }
        }

        $prompt .= "CONTENT LIMITS (strictly adhere to these):\n";
        $prompt .= "- Title: maximum {$titleWordLimit} words\n";
        $prompt .= "- Description: maximum {$descriptionWordLimit} words\n";
        $prompt .= "- Content: maximum {$contentWordLimit} words\n";
        $prompt .= "- Tags: exactly {$tagsCount} relevant tags\n";
        $prompt .= "- SEO Title: maximum {$seoTitleLimit} characters\n";
        $prompt .= "- SEO Description: maximum {$seoDescriptionLimit} characters\n\n";

        $prompt .= "Format the translated content as JSON with this structure:\n";
        $prompt .= "{\n";
        $prompt .= '  "title": "Translated title (max ' . $titleWordLimit . ' words)",'."\n";
        $prompt .= '  "description": "Translated description (max ' . $descriptionWordLimit . ' words)",'."\n";
        $prompt .= '  "content": "Translated content in HTML format (max ' . $contentWordLimit . ' words)",'."\n";
        $prompt .= '  "tags": ["tag1", "tag2", "tag3"] (exactly ' . $tagsCount . ' tags in ' . $targetLanguageName . '),'."\n";
        $prompt .= '  "seo_title": "SEO optimized title (max ' . $seoTitleLimit . ' characters)",'."\n";
        $prompt .= '  "seo_description": "SEO meta description (max ' . $seoDescriptionLimit . ' characters)",'."\n";
        $prompt .= '  "detected_source_language": "Detected language of source content"'."\n";
        $prompt .= "}\n\n";

        if ($isDirectTranslation) {
            $prompt .= "CRITICAL: Translate EXACTLY what is provided. Do not add any extra content, explanations, or enhancements. ";
            $prompt .= "Keep translations concise and true to the original length and meaning. ";
            $prompt .= "If the original content is short, keep the translation short. ";
            $prompt .= "Only translate the text that is actually provided in the source content.";
        } else {
            $prompt .= "CRITICAL: Make the translation sound completely natural and native in {$targetLanguageName}. ";
            $prompt .= "A native {$targetLanguageName} speaker should never suspect this was translated from another language. ";
            $prompt .= "Adapt cultural nuances, local expressions, and market-specific terminology appropriately.";
        }

        return $prompt;
    }

    /**
     * Build prompt for product content generation
     */
    protected function buildProductPrompt(string $title, string $description, array $options): string
    {
        // Get word limits from settings - Product specific limits (shorter, punchier)
        $titleWordLimit = (int) ai_setting('title_word_limit', 8);
        $descriptionWordLimit = (int) ai_setting('description_word_limit', 30);
        $contentWordLimit = (int) ai_setting('content_word_limit', 300);
        $tagsCount = (int) ai_setting('tags_count_limit', 5);
        $seoTitleLimit = (int) ai_setting('seo_title_limit', 60);
        $seoDescriptionLimit = (int) ai_setting('seo_description_limit', 160);
        
        $language = $options['language'] ?? 'en';
        $style = $options['style'] ?? 'professional';
        
        $prompt = "Generate comprehensive e-commerce product content for:\n\n";
        $prompt .= "Product Title: {$title}\n";
        
        if ($description) {
            $prompt .= "Brief Description: {$description}\n";
        }
        
        $prompt .= "Language: {$language}\n";
        $prompt .= "Writing Style: {$style}\n\n";
        
        $prompt .= "CRITICAL: Write in a natural, human-friendly tone that doesn't sound AI-generated. Follow these guidelines:\n";
        $prompt .= "- Use conversational language that real people use when talking about products\n";
        $prompt .= "- Add personal touches and emotional connections to help customers relate\n";
        $prompt .= "- Mix short punchy sentences with longer descriptive ones for natural flow\n";
        $prompt .= "- Focus on real benefits customers actually care about in their daily lives\n";
        $prompt .= "- Include practical usage scenarios and specific examples\n";
        $prompt .= "- Use sensory words and details that help customers visualize using the product\n";
        $prompt .= "- Write like a knowledgeable friend recommending the product, not a salesperson\n";
        $prompt .= "- Avoid corporate jargon, buzzwords, or overly promotional language\n";
        $prompt .= "- Make it sound authentic and trustworthy, not robotic or artificial\n\n";
        
        $prompt .= "Please generate content with these specific requirements:\n";
        
        // Only include fields that are requested
        if ($options['generate_title'] ?? true) {
            $prompt .= "1. Product title/name (maximum {$titleWordLimit} words)\n";
        }
        if ($options['generate_description'] ?? true) {
            $prompt .= "2. Short product description for listings (maximum {$descriptionWordLimit} words)\n";
        }
        if ($options['generate_content'] ?? true) {
            $prompt .= "3. Detailed product content with features and benefits (maximum {$contentWordLimit} words)\n";
        }
        if ($options['generate_tags'] ?? false) {
            $prompt .= "4. Relevant product tags/features (exactly {$tagsCount} tags)\n";
        }
        if ($options['generate_seo_title'] ?? false) {
            $prompt .= "5. SEO-optimized title (maximum {$seoTitleLimit} characters)\n";
        }
        if ($options['generate_seo_description'] ?? false) {
            $prompt .= "6. SEO meta description (maximum {$seoDescriptionLimit} characters)\n";
        }
        
        $prompt .= "\nFormat the response as JSON with the following structure:\n";
        $prompt .= "{\n";
        
        if ($options['generate_title'] ?? true) {
            $prompt .= '  "title": "Product title/name (max ' . $titleWordLimit . ' words)",'."\n";
        }
        if ($options['generate_description'] ?? true) {
            $prompt .= '  "description": "Short product description (max ' . $descriptionWordLimit . ' words)",'."\n";
        }
        if ($options['generate_content'] ?? true) {
            $prompt .= '  "content": "Detailed product content in HTML format (max ' . $contentWordLimit . ' words)",'."\n";
        }
        if ($options['generate_tags'] ?? false) {
            $prompt .= '  "tags": ["tag1", "tag2", "tag3"] (exactly ' . $tagsCount . ' tags),'."\n";
        }
        if ($options['generate_seo_title'] ?? false) {
            $prompt .= '  "seo_title": "SEO optimized title (max ' . $seoTitleLimit . ' characters)",'."\n";
        }
        if ($options['generate_seo_description'] ?? false) {
            $prompt .= '  "seo_description": "SEO meta description (max ' . $seoDescriptionLimit . ' characters)",'."\n";
        }
        
        $prompt = rtrim($prompt, ",\n") . "\n";
        $prompt .= "}\n\n";
        
        $prompt .= "REMEMBER: Strictly adhere to the word/character limits specified. Write naturally and authentically - imagine you're explaining this product to a friend who's considering buying it. Use real-world scenarios, specific benefits, and genuine enthusiasm. Make it feel human-written, not generated. Use HTML formatting for the content field with proper headings (<h2>, <h3>) and structure.";
        
        return $prompt;
    }

    /**
     * Build prompt for blog content generation
     */
    protected function buildBlogPrompt(string $title, string $description, array $options): string
    {
        // Get word limits from settings - Blog specific limits (longer, comprehensive)
        $titleWordLimit = (int) ai_setting('blog_title_word_limit', 15);
        $descriptionWordLimit = (int) ai_setting('blog_description_word_limit', 80);
        $contentWordLimit = (int) ai_setting('blog_content_word_limit', 1200);
        $tagsCount = (int) ai_setting('blog_tags_count_limit', 8);
        $seoTitleLimit = (int) ai_setting('blog_seo_title_limit', 60);
        $seoDescriptionLimit = (int) ai_setting('blog_seo_description_limit', 160);
        
        $language = $options['language'] ?? 'en';
        $style = $options['style'] ?? 'informative';
        
        $prompt = "Generate comprehensive blog post content for:\n\n";
        $prompt .= "Blog Title: {$title}\n";
        
        if ($description) {
            $prompt .= "Brief Description: {$description}\n";
        }
        
        $prompt .= "Language: {$language}\n";
        $prompt .= "Writing Style: {$style}\n\n";
        
        $prompt .= "CRITICAL: Write in a natural, human-friendly tone that doesn't sound AI-generated. Follow these guidelines:\n";
        $prompt .= "- Use conversational, authentic language that real bloggers and writers use\n";
        $prompt .= "- Include personal insights, opinions, and relatable experiences\n";
        $prompt .= "- Vary sentence structure for natural rhythm (short impactful sentences mixed with longer explanatory ones)\n";
        $prompt .= "- Add storytelling elements and real-world examples when appropriate\n";
        $prompt .= "- Use a genuine, engaging voice that connects with readers emotionally\n";
        $prompt .= "- Include practical tips and actionable advice that readers can actually use\n";
        $prompt .= "- Write like a knowledgeable person sharing valuable insights, not an AI system\n";
        $prompt .= "- Avoid buzzwords, corporate speak, or overly technical jargon unless necessary\n";
        $prompt .= "- Make it feel like content a real human expert would write and publish\n\n";
        
        $prompt .= "BLOG FORMATTING REQUIREMENTS:\n";
        $prompt .= "- Create comprehensive, well-researched content with depth and value\n";
        $prompt .= "- Use proper blog structure: engaging hook introduction, well-organized body sections, compelling conclusion\n";
        $prompt .= "- Include descriptive subheadings (H2, H3) that tell a story and break up content for easy scanning\n";
        $prompt .= "- Add bullet points, numbered lists, and formatting for better readability and quick scanning\n";
        $prompt .= "- Include actionable takeaways, practical tips, and step-by-step advice readers can implement immediately\n";
        $prompt .= "- Make content scannable with clear sections, logical flow, and visual breaks\n";
        $prompt .= "- Add relevant examples, case studies, or real-world scenarios to illustrate points\n";
        $prompt .= "- Include questions, quotes, or statistics to engage readers and add credibility\n";
        $prompt .= "- End with a compelling conclusion that summarizes key points and provides next steps\n";
        $prompt .= "- Optimize for both human readers and search engines with natural keyword integration\n\n";
        
        $prompt .= "Please generate content with these specific requirements:\n";
        
        // Only include fields that are requested
        if ($options['generate_title'] ?? true) {
            $prompt .= "1. Blog post title (maximum {$titleWordLimit} words)\n";
        }
        if ($options['generate_description'] ?? true) {
            $prompt .= "2. Blog post excerpt/description (maximum {$descriptionWordLimit} words)\n";
        }
        if ($options['generate_content'] ?? true) {
            $prompt .= "3. Complete blog post content (maximum {$contentWordLimit} words)\n";
        }
        if ($options['generate_tags'] ?? false) {
            $prompt .= "4. Relevant tags/categories (exactly {$tagsCount} tags)\n";
        }
        if ($options['generate_seo_title'] ?? false) {
            $prompt .= "5. SEO-optimized title (maximum {$seoTitleLimit} characters)\n";
        }
        if ($options['generate_seo_description'] ?? false) {
            $prompt .= "6. SEO meta description (maximum {$seoDescriptionLimit} characters)\n";
        }
        
        $prompt .= "\nFormat the response as JSON with the following structure:\n";
        $prompt .= "{\n";
        
        if ($options['generate_title'] ?? true) {
            $prompt .= '  "title": "Blog post title (max ' . $titleWordLimit . ' words)",'."\n";
        }
        if ($options['generate_description'] ?? true) {
            $prompt .= '  "description": "Blog post excerpt/description (max ' . $descriptionWordLimit . ' words)",'."\n";
        }
        if ($options['generate_content'] ?? true) {
            $prompt .= '  "content": "Complete blog post content in HTML format (max ' . $contentWordLimit . ' words)",'."\n";
        }
        if ($options['generate_tags'] ?? false) {
            $prompt .= '  "tags": ["tag1", "tag2", "tag3"] (exactly ' . $tagsCount . ' tags),'."\n";
        }
        if ($options['generate_seo_title'] ?? false) {
            $prompt .= '  "seo_title": "SEO optimized title (max ' . $seoTitleLimit . ' characters)",'."\n";
        }
        if ($options['generate_seo_description'] ?? false) {
            $prompt .= '  "seo_description": "SEO meta description (max ' . $seoDescriptionLimit . ' characters)"'."\n";
        }
        
        $prompt .= "}\n\n";
        $prompt .= "IMPORTANT: Ensure all content is natural, engaging, and doesn't sound robotic or AI-generated. ";
        $prompt .= "Write like a human expert who genuinely cares about providing value to readers.";
        
        return $prompt;
    }

    /**
     * Core method to generate content using AI API
     */
    protected function generateContent(string $prompt, string $type): array
    {
        if (!$this->isAvailable()) {
            return [
                'success' => false,
                'error' => 'AI service is not available. Please configure your API key in settings.'
            ];
        }

        // Check rate limits
        if (!$this->checkRateLimit()) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again later.'
            ];
        }

        // Get token limits based on type
        $maxTokens = $this->getMaxTokensForType($type);
        $retryAttempts = (int) ai_setting('retry_attempts', 3);

        for ($attempt = 1; $attempt <= $retryAttempts; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($this->apiUrl . '/chat/completions', [
                        'model' => ai_setting('model', 'gpt-3.5-turbo'),
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $prompt
                            ]
                        ],
                        'max_tokens' => $maxTokens,
                        'temperature' => (float) ai_setting('temperature', 0.7),
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $content = $data['choices'][0]['message']['content'] ?? '';
                    
                    // Clean up the response - remove markdown code blocks if present
                    $cleanContent = $content;
                    if (preg_match('/```(?:json)?\s*(.*?)```/s', $content, $matches)) {
                        $cleanContent = trim($matches[1]);
                    }
                    
                    // Try to parse JSON response
                    $parsedContent = json_decode($cleanContent, true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && is_array($parsedContent)) {
                        // Cache the result if enabled
                        if (ai_setting('enable_caching', '1') === '1') {
                            $cacheKey = 'ai_content_' . md5($prompt);
                            $cacheTtl = (int) ai_setting('cache_ttl', 3600);
                            cache()->put($cacheKey, $parsedContent, $cacheTtl);
                        }
                        
                        return [
                            'success' => true,
                            'data' => $parsedContent,
                            'tokens_used' => $data['usage']['total_tokens'] ?? 0
                        ];
                    } else {
                        if ($attempt === $retryAttempts) {
                            return [
                                'success' => false,
                                'error' => 'Invalid response format from AI service'
                            ];
                        }
                        continue; // Retry
                    }
                } else {
                    if ($attempt === $retryAttempts) {
                        return [
                            'success' => false,
                            'error' => 'API request failed after ' . $retryAttempts . ' attempts'
                        ];
                    }
                    sleep(1); // Wait before retry
                }
            } catch (\Exception $e) {
                if ($attempt === $retryAttempts) {
                    // Log error if logging is enabled
                    if (ai_setting('enable_logging', '1') === '1') {
                        \Log::error('AI Content Generator Error: ' . $e->getMessage());
                    }
                    
                    return [
                        'success' => false,
                        'error' => 'Service temporarily unavailable'
                    ];
                }
                sleep(1); // Wait before retry
            }
        }

        return [
            'success' => false,
            'error' => 'Maximum retry attempts exceeded'
        ];
    }

    /**
     * Get maximum tokens for content type
     */
    protected function getMaxTokensForType(string $type): int
    {
        switch ($type) {
            case 'product':
                return (int) ai_setting('max_tokens_product', 900);
            case 'blog':
            case 'post':
                return (int) ai_setting('max_tokens_post', 2400);
            case 'translation':
                return (int) ai_setting('max_tokens_translation', 3000);
            default:
                return (int) ai_setting('max_tokens', 2000);
        }
    }

    /**
     * Get API status
     */
    public function getApiStatus(): array
    {
        if (!$this->isAvailable()) {
            return [
                'available' => false,
                'message' => 'API key or URL not configured'
            ];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ])
                ->get($this->apiUrl . '/models');

            if ($response->successful()) {
                return [
                    'available' => true,
                    'message' => 'API is working correctly'
                ];
            } else {
                return [
                    'available' => false,
                    'message' => 'API request failed: ' . $response->status()
                ];
            }
        } catch (\Exception $e) {
            return [
                'available' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get today's usage statistics
     */
    public function getTodayUsage(): array
    {
        // For now, return mock data since we don't have usage tracking implemented
        // In a real implementation, you would track API calls in database
        return [
            'requests_today' => 0,
            'tokens_used_today' => 0,
            'cost_today' => 0.00,
            'remaining_requests' => 1000,
            'last_request_time' => null
        ];
    }
}
