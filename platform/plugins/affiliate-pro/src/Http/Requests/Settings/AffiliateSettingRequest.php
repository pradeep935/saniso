<?php

namespace Botble\AffiliatePro\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Language\Facades\Language;
use Botble\Support\Http\Requests\Request;

class AffiliateSettingRequest extends Request
{
    public function rules(): array
    {
        $rules = [
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'cookie_lifetime' => 'required|numeric|min:1',
            'minimum_withdrawal_amount' => 'required|numeric|min:0',
            'enable_registration' => [new OnOffRule()],
            'auto_approve_affiliates' => [new OnOffRule()],
            'auto_approve_commissions' => [new OnOffRule()],
            'enable_commission_for_each_category' => 'sometimes|in:0,1',
            'payout_methods' => 'sometimes|array',
            'payout_methods.bank_transfer' => ['sometimes', new OnOffRule()],
            'payout_methods.paypal' => ['sometimes', new OnOffRule()],
            'payout_methods.stripe' => ['sometimes', new OnOffRule()],
            'payout_methods.other' => ['sometimes', new OnOffRule()],
        ];

        if (is_plugin_active('language')) {
            // Add validation for multilingual affiliate rules content
            $supportedLocales = Language::getSupportedLocales();
            $defaultLanguage = Language::getDefaultLanguage();
            $defaultLocale = $defaultLanguage['lang_locale'] ?? 'en';

            foreach ($supportedLocales as $locale => $language) {
                if ($locale === $defaultLocale) {
                    // Default language uses 'affiliate_rules_content' without locale suffix
                    $rules['affiliate_rules_content'] = 'nullable|string';
                } else {
                    // Other languages use locale-specific keys
                    $rules["affiliate_rules_content_{$locale}"] = 'nullable|string';
                }
            }
        } else {
            $rules['affiliate_rules_content'] = 'nullable|string';
        }

        if ($this->input('enable_commission_for_each_category')) {
            // Validate request setting category commission
            $commissionByCategory = $this->input('commission_by_category');
            if ($commissionByCategory) {
                foreach ($commissionByCategory as $key => $item) {
                    $commissionFeeName = sprintf('%s.%s.commission_percentage', 'commission_by_category', $key);
                    $categoryName = sprintf('%s.%s.categories', 'commission_by_category', $key);
                    $rules[$commissionFeeName] = 'required|numeric|min:0|max:100';
                    $rules[$categoryName] = 'required';
                }
            }
        }

        return $rules;
    }

    public function attributes(): array
    {
        $attributes = [];

        if ($this->input('enable_commission_for_each_category') == 1) {
            // Validate request setting category commission
            $commissionByCategory = $this->input('commission_by_category');
            if ($commissionByCategory) {
                foreach ($commissionByCategory as $key => $item) {
                    $commissionFeeName = sprintf('%s.%s.commission_percentage', 'commission_by_category', $key);
                    $categoryName = sprintf('%s.%s.categories', 'commission_by_category', $key);
                    $attributes[$commissionFeeName] = trans('plugins/affiliate-pro::settings.commission_percentage_each_category_name', ['key' => $key]);
                    $attributes[$categoryName] = trans('plugins/affiliate-pro::settings.commission_percentage_each_category_categories', ['key' => $key]);
                }
            }
        }

        return $attributes;
    }
}
