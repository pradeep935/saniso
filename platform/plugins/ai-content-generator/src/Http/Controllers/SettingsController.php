<?php

namespace Botble\AIContentGenerator\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\AIContentGenerator\Http\Requests\SettingsRequest;
use Botble\AIContentGenerator\Services\AIContentService;
use Botble\AIContentGenerator\Models\AIContentGeneratorSetting;
use Botble\Setting\Supports\SettingStore;

class SettingsController extends BaseController
{
    protected AIContentService $aiService;

    public function __construct(AIContentService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function index()
    {
        page_title()->setTitle(trans('plugins/ai-content-generator::ai-content-generator.settings.title'));

        $availableLanguages = [
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
        ];

        $writingStyles = [
            'professional' => 'Professional',
            'casual' => 'Casual',
            'technical' => 'Technical',
            'luxury' => 'Luxury',
            'informative' => 'Informative',
            'persuasive' => 'Persuasive',
            'storytelling' => 'Storytelling',
            'conversational' => 'Conversational',
        ];

        // Get today's usage statistics
        $todayUsage = $this->aiService->getTodayUsage();

        return view('plugins/ai-content-generator::settings', compact('availableLanguages', 'writingStyles', 'todayUsage'));
    }

    public function update(SettingsRequest $request, BaseHttpResponse $response)
    {
        $data = $request->except(['_token']);
        
        // Handle enabled_languages specially - simple checkbox array format
        $enabledLanguages = $request->input('enabled_languages', []);
        
        // If no languages selected, default to English and Dutch
        if (empty($enabledLanguages)) {
            $enabledLanguages = ['en', 'nl'];
        }
        
        // Convert array to comma-separated string for storage
        if (is_array($enabledLanguages)) {
            $enabledLanguagesString = implode(',', $enabledLanguages);
        } else {
            $enabledLanguagesString = 'en,nl'; // fallback
        }
        
        $data['enabled_languages'] = $enabledLanguagesString;
        
        // Save each setting to our own table
        foreach ($data as $settingKey => $settingValue) {
            // Skip our internal indicator fields
            if (in_array($settingKey, ['enabled_languages_empty', 'enabled_languages_present'])) {
                continue;
            }
            
            // Handle boolean checkboxes (these work because they have hidden inputs with value "0")
            if (in_array($settingKey, ['enable_for_products', 'enable_for_blogs', 'auto_populate_tags', 'show_word_count'])) {
                $settingValue = (bool) $settingValue ? '1' : '0';
            }
            // Handle other array values (like custom_languages)
            elseif (is_array($settingValue)) {
                $settingValue = json_encode($settingValue);
            }
            // Handle all other settings (text, select, textarea, etc.)
            else {
                // Convert to string and trim
                $settingValue = trim((string) $settingValue);
            }

            // Save to our database
            AIContentGeneratorSetting::set($settingKey, $settingValue);
        }
        
        return $response
            ->setPreviousUrl(route('ai-content-generator.settings'))
            ->setMessage('Settings saved successfully!');
    }
}
