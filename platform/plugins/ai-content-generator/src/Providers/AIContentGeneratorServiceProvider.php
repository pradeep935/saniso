<?php

namespace Botble\AIContentGenerator\Providers;

use Botble\Base\Facades\Assets;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Forms\FormAbstract;
use Botble\AIContentGenerator\Services\AIContentService;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Database\Eloquent\Model;

class AIContentGeneratorServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(AIContentService::class, function ($app) {
            return new AIContentService();
        });
    }

    public function boot(): void
    {
        // Load helper functions first
        if (file_exists($helpers = __DIR__ . '/../Helpers/helpers.php')) {
            require_once $helpers;
        }
        
        $this->setNamespace('plugins/ai-content-generator')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['ai-content-generator', 'permissions'])
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->loadRoutes()
            ->publishAssets();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Register menu safely after routes are matched
        $this->app['events']->listen(RouteMatched::class, function () {
            $this->registerMenu();
        });

        // Register hooks for form integration
        $this->registerFormHooks();
        
        // Asset injection for admin pages
        $this->registerAssets();
    }

    protected function registerMenu(): void
    {
        if (function_exists('dashboard_menu')) {
            dashboard_menu()
                ->registerItem([
                    'id' => 'cms-plugins-ai-content-generator',
                    'priority' => 999,
                    'parent_id' => 'cms-core-settings',
                    'name' => 'AI Content Generator',
                    'icon' => 'fas fa-robot',
                    'url' => route('ai-content-generator.settings'),
                    'permissions' => ['ai-content-generator.settings'],
                ]);
        }
    }

    protected function registerFormHooks(): void
    {
        // Register our AI content generator as a translatable meta box first
        // so it appears on all language forms
        if (class_exists('\Botble\LanguageAdvanced\Supports\LanguageAdvancedManager')) {
            \Botble\LanguageAdvanced\Supports\LanguageAdvancedManager::addTranslatableMetaBox('ai_content_generator');
        }
        
        // Use a higher priority than language-advanced (1134) to ensure our hook runs after
        add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [$this, 'addAIContentToForm'], 1200, 2);
        
        // Hook into the language-advanced system to preserve our field across language tabs
        add_filter(BASE_FILTER_BEFORE_RENDER_FORM, [$this, 'preserveAIFieldInLanguageForms'], 1300, 2);
    }

    public function addAIContentToForm(FormAbstract $form, $data): FormAbstract
    {
        // Get the entity type (product or post)
        $entityType = $this->getEntityType(get_class($data));
        
        if (empty($entityType)) {
            return $form;
        }

        // Check if we should show AI for this entity type
        if (($entityType === 'post' && !ai_setting('enable_for_blogs', true)) ||
            ($entityType === 'product' && !ai_setting('enable_for_products', true))) {
            return $form;
        }

        // Try to get form fields safely
        try {
            $allFields = $form->getFields();
            $appendPosition = $this->getAppendPosition($allFields);
            
            // Add our AI content generator field before a suitable position
            $form->addBefore($appendPosition, 'ai_content_generator', 'html', [
                'label' => 'AI Content Generator',
                'label_attr' => ['class' => 'control-label'],
                'template' => 'plugins/ai-content-generator::form-field',
                'value' => null,
                'data' => [
                    'entityType' => $entityType,
                    'entityData' => $data,
                    'isAvailable' => app(AIContentService::class)->isAvailable(),
                    'languages' => $this->getAvailableLanguages(),
                    'defaultLanguage' => ai_setting('default_language', 'en'),
                    'writingStyles' => $this->getWritingStyles(),
                    'wordLimits' => $this->getWordLimitsForEntity($entityType),
                ],
            ]);
        } catch (\Exception $e) {
            // If addBefore fails, try simple add
            $form->add('ai_content_generator', 'html', [
                'label' => 'AI Content Generator',
                'label_attr' => ['class' => 'control-label'],
                'template' => 'plugins/ai-content-generator::form-field',
                'value' => null,
                'data' => [
                    'entityType' => $entityType,
                    'entityData' => $data,
                    'isAvailable' => app(AIContentService::class)->isAvailable(),
                    'languages' => $this->getAvailableLanguages(),
                    'defaultLanguage' => ai_setting('default_language', 'en'),
                    'writingStyles' => $this->getWritingStyles(),
                    'wordLimits' => $this->getWordLimitsForEntity($entityType),
                ],
            ]);
        }

        return $form;
    }

    public function preserveAIFieldInLanguageForms(FormAbstract $form, $data): FormAbstract
    {
        // If this is a language-advanced form (non-default language), ensure our AI field is preserved
        if (class_exists('\Botble\Language\Facades\Language') && 
            method_exists('\Botble\Language\Facades\Language', 'getCurrentAdminLocaleCode') &&
            method_exists('\Botble\Language\Facades\Language', 'getDefaultLocaleCode')) {
            
            $currentLocale = \Botble\Language\Facades\Language::getCurrentAdminLocaleCode();
            $defaultLocale = \Botble\Language\Facades\Language::getDefaultLocaleCode();
            
            // If we're not on the default language and our field was removed, add it back
            if ($currentLocale !== $defaultLocale && !$form->has('ai_content_generator')) {
                // Get the entity type
                $entityType = $this->getEntityType(get_class($data));
                
                if (!empty($entityType) && 
                    (($entityType === 'post' && ai_setting('enable_for_blogs', true)) ||
                     ($entityType === 'product' && ai_setting('enable_for_products', true)))) {
                    
                    // Re-add our AI content generator field
                    $form->add('ai_content_generator', 'html', [
                        'label' => 'AI Content Generator',
                        'label_attr' => ['class' => 'control-label'],
                        'template' => 'plugins/ai-content-generator::form-field',
                        'value' => null,
                        'data' => [
                            'entityType' => $entityType,
                            'entityData' => $data,
                            'isAvailable' => app(AIContentService::class)->isAvailable(),
                            'languages' => $this->getAvailableLanguages(),
                            'defaultLanguage' => ai_setting('default_language', 'en'),
                            'writingStyles' => $this->getWritingStyles(),
                            'wordLimits' => $this->getWordLimitsForEntity($entityType),
                        ],
                    ]);
                }
            }
        }
        
        return $form;
    }

    private function getEntityType($entityClass): string
    {
        $entitySupported = [
            'Botble\Ecommerce\Models\Product' => 'product',
            'Botble\Blog\Models\Post' => 'post',
        ];

        return data_get($entitySupported, $entityClass, '');
    }

    private function getAppendPosition(array $allFields): string
    {
        // Check if we're on a language tab (ref_lang parameter exists)
        $refLang = request()->get('ref_lang');
        
        if ($refLang) {
            // For language tabs, prioritize positioning early in the form since there are fewer fields
            
            // First priority: after title/name field (most likely to exist)
            if (isset($allFields['name'])) {
                return 'name';
            }
            
            if (isset($allFields['title'])) {
                return 'title';
            }
            
            // Second priority: after save/submit buttons if title not found
            if (isset($allFields['submit'])) {
                return 'submit';
            }
            
            if (isset($allFields['save'])) {
                return 'save';
            }
            
            // Third priority: after status field (publish card)
            if (isset($allFields['status'])) {
                return 'status';
            }
            
            // Fourth priority: after is_featured field
            if (isset($allFields['is_featured'])) {
                return 'is_featured';
            }
            
            // If nothing found, put at the beginning by returning the first field
            if (!empty($allFields)) {
                return array_key_first($allFields);
            }
        }
        
        // Default positioning for main language (original behavior)
        if (isset($allFields['categories[]'])) {
            return 'categories[]';
        }
        
        if (isset($allFields['status'])) {
            return 'status';
        }

        // Fallback to the last field if it exists
        if (!empty($allFields)) {
            return array_key_last($allFields);
        }
        
        // If no fields exist, add after the name field (fallback)
        return 'name';
    }

    protected function shouldShowAIForBlog(): bool
    {
        // Check if plugin is enabled and blog feature is enabled
        $apiKey = ai_setting('api_key');
        $enableForBlogs = ai_setting('enable_for_blogs', '1');
        
        return !empty($apiKey) && $enableForBlogs === '1';
    }

    protected function shouldShowAIForProduct(): bool
    {
        // Check if plugin is enabled and product feature is enabled
        $apiKey = ai_setting('api_key');
        $enableForProducts = ai_setting('enable_for_products', '1');
        
        return !empty($apiKey) && $enableForProducts === '1';
    }

    protected function getAvailableLanguages(): array
    {
        // Get enabled languages using our custom function - now comma-separated format
        $enabledLanguagesSetting = ai_setting('enabled_languages', 'en,nl');
        
        // Convert comma-separated string to array
        if (is_string($enabledLanguagesSetting)) {
            $enabledLanguages = array_filter(array_map('trim', explode(',', $enabledLanguagesSetting)));
        } else {
            $enabledLanguages = ['en', 'nl']; // fallback
        }
        
        if (empty($enabledLanguages)) {
            $enabledLanguages = ['en', 'nl'];
        }

        // Base languages (simplified set matching our settings page)
        $allLanguages = [
            'en' => 'English',
            'nl' => 'Dutch',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'it' => 'Italian',
            'pt' => 'Portuguese'
        ];

        // Build result with only enabled languages
        $result = [];
        foreach ($enabledLanguages as $code) {
            if (isset($allLanguages[$code])) {
                $result[$code] = $allLanguages[$code];
            }
        }

        // Ensure we always have at least English if nothing is selected
        if (empty($result)) {
            $result['en'] = 'English';
        }

        return $result;
    }

    protected function getWritingStyles(): array
    {
        return [
            'informative' => 'Informative',
            'persuasive' => 'Persuasive',
            'storytelling' => 'Storytelling',
            'technical' => 'Technical',
            'conversational' => 'Conversational',
            'professional' => 'Professional',
            'casual' => 'Casual',
            'luxury' => 'Luxury'
        ];
    }

    protected function getWordLimitsForEntity(string $entityType): array
    {
        if ($entityType === 'post') {
            // Blog post limits (longer, comprehensive content)
            return [
                'title' => (int) ai_setting('blog_title_word_limit', 15),
                'description' => (int) ai_setting('blog_description_word_limit', 80),
                'content' => (int) ai_setting('blog_content_word_limit', 1200),
                'tags' => (int) ai_setting('blog_tags_count_limit', 8),
                'seoTitle' => (int) ai_setting('blog_seo_title_limit', 60),
                'seoDescription' => (int) ai_setting('blog_seo_description_limit', 160),
            ];
        } else {
            // Product limits (shorter, punchy content)
            return [
                'title' => (int) ai_setting('title_word_limit', 8),
                'description' => (int) ai_setting('description_word_limit', 30),
                'content' => (int) ai_setting('content_word_limit', 300),
                'tags' => (int) ai_setting('tags_count_limit', 5),
                'seoTitle' => (int) ai_setting('seo_title_limit', 60),
                'seoDescription' => (int) ai_setting('seo_description_limit', 160),
            ];
        }
    }

    protected function registerAssets(): void
    {
        // Assets will be loaded directly in the view files to avoid 404 errors
        // This ensures the plugin works without requiring asset publishing
    }
}
