<?php

namespace Botble\AffiliatePro\Forms\Settings;

use Botble\AffiliatePro\Facades\AffiliateHelper;
use Botble\AffiliatePro\Http\Requests\Settings\AffiliateSettingRequest;
use Botble\Base\Facades\Assets;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Supports\Editor;
use Botble\Language\Facades\Language;
use Botble\Setting\Forms\SettingForm;
use Illuminate\Support\Facades\App;

class AffiliateSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css')
            ->addScriptsDirectly([
                'vendor/core/core/base/libraries/tagify/tagify.js',
                'vendor/core/core/base/js/tags.js',
                'vendor/core/plugins/affiliate-pro/js/affiliate-setting.js',
            ]);

        (new Editor())->registerAssets();

        $commissionEachCategory = [];

        if (AffiliateHelper::isCommissionCategoryFeeBasedEnabled()) {
            $commissionEachCategory = AffiliateHelper::getCommissionEachCategory();
        }

        $this
            ->setSectionTitle(trans('plugins/affiliate-pro::settings.title'))
            ->setSectionDescription(trans('plugins/affiliate-pro::settings.description'))
            ->setValidatorClass(AffiliateSettingRequest::class)
            ->setMethod('PUT')
            ->contentOnly()
            ->add('commission_percentage', 'number', [
                'label' => trans('plugins/affiliate-pro::settings.default_commission_percentage'),
                'value' => AffiliateHelper::getSetting('commission_percentage', 10),
                'attr' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ])
            ->add('enable_commission_for_each_category', OnOffCheckboxField::class, [
                'label' => trans('plugins/affiliate-pro::settings.enable_commission_for_each_category'),
                'value' => AffiliateHelper::isCommissionCategoryFeeBasedEnabled(),
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.category-commission-settings',
                ],
            ])
            ->add('category_commission_fields', 'html', [
                'html' => view(
                    'plugins/affiliate-pro::settings.partials.category-commission-fields',
                    compact('commissionEachCategory')
                )->render(),
            ])
            ->add('cookie_lifetime', 'number', [
                'label' => trans('plugins/affiliate-pro::settings.cookie_lifetime'),
                'value' => AffiliateHelper::getSetting('cookie_lifetime', 30),
                'attr' => [
                    'min' => 1,
                ],
            ])
            ->add(
                'minimum_withdrawal_amount',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.minimum_withdrawal_amount'))
                    ->helperText(trans('plugins/affiliate-pro::settings.minimum_withdrawal_amount_helper'))
                    ->value(AffiliateHelper::getSetting('minimum_withdrawal_amount', 50))
            )
            ->add(
                'enable_registration',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.enable_registration'))
                    ->helperText(trans('plugins/affiliate-pro::settings.enable_registration_helper'))
                    ->value(AffiliateHelper::getSetting('enable_registration', true))
            )
            ->add(
                'auto_approve_affiliates',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.auto_approve_affiliates'))
                    ->helperText(trans('plugins/affiliate-pro::settings.auto_approve_affiliates_helper'))
                    ->value(AffiliateHelper::getSetting('auto_approve_affiliates', false))
            )
            ->add(
                'auto_approve_commissions',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.auto_approve_commissions'))
                    ->helperText(trans('plugins/affiliate-pro::settings.auto_approve_commissions_helper'))
                    ->value(AffiliateHelper::getSetting('auto_approve_commissions', false))
            )
            ->add('affiliate_rules_content_section', 'html', [
                'html' => $this->getAffiliateRulesContentFields(),
            ])
            ->add('withdrawal_payment_methods_section', 'html', [
                'html' => '<h4 class="mt-4">' . trans('plugins/affiliate-pro::settings.withdrawal_payment_methods_section') . '</h4>',
            ])
            ->add(
                'payout_methods[bank_transfer]',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.enable_bank_transfer'))
                    ->value(AffiliateHelper::getSetting('payout_methods.bank_transfer', true))
            )
            ->add(
                'payout_methods[paypal]',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.enable_paypal'))
                    ->value(AffiliateHelper::getSetting('payout_methods.paypal', true))
            )
            ->add(
                'payout_methods[stripe]',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.enable_stripe'))
                    ->value(AffiliateHelper::getSetting('payout_methods.stripe', false))
            )
            ->add(
                'payout_methods[other]',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.enable_other'))
                    ->value(AffiliateHelper::getSetting('payout_methods.other', true))
            )
            ->add('promotional_banners_section', 'html', [
                'html' => '<h4 class="mt-4">' . trans('plugins/affiliate-pro::settings.promotional_banners_section') . '</h4>',
            ])
            ->add(
                'banner_1_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.banner_1_name'))
                    ->helperText(trans('plugins/affiliate-pro::settings.banner_1_name_helper'))
                    ->value(AffiliateHelper::getSetting('banner_1_name', 'Banner 1 (468x60)'))
            )
            ->add(
                'banner_1_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.banner_1_image'))
                    ->helperText(trans('plugins/affiliate-pro::settings.banner_1_image_helper'))
                    ->value(AffiliateHelper::getSetting('banner_1_image'))
            )
            ->add(
                'banner_2_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.banner_2_name'))
                    ->helperText(trans('plugins/affiliate-pro::settings.banner_2_name_helper'))
                    ->value(AffiliateHelper::getSetting('banner_2_name', 'Banner 2 (300x250)'))
            )
            ->add(
                'banner_2_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.banner_2_image'))
                    ->helperText(trans('plugins/affiliate-pro::settings.banner_2_image_helper'))
                    ->value(AffiliateHelper::getSetting('banner_2_image'))
            )
            ->add(
                'banner_3_name',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.banner_3_name'))
                    ->helperText(trans('plugins/affiliate-pro::settings.banner_3_name_helper'))
                    ->value(AffiliateHelper::getSetting('banner_3_name', 'Banner 3 (728x90)'))
            )
            ->add(
                'banner_3_image',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('plugins/affiliate-pro::settings.banner_3_image'))
                    ->helperText(trans('plugins/affiliate-pro::settings.banner_3_image_helper'))
                    ->value(AffiliateHelper::getSetting('banner_3_image'))
            );
    }

    protected function getAffiliateRulesContentFields(): string
    {
        $value = AffiliateHelper::getSetting('rules_content', '');

        $defaultRuleContent = view('plugins/affiliate-pro::settings.partials.affiliate-rules-single', [
            'locale' => App::getLocale(),
            'value' => $value,
            'isDefault' => true,
        ])->render();

        if (! is_plugin_active('language')) {
            return $defaultRuleContent;
        }

        $supportedLocales = Language::getSupportedLocales();
        $defaultLanguage = Language::getDefaultLanguage();
        $defaultLocale = $defaultLanguage['lang_locale'] ?? 'en';

        if (count($supportedLocales) <= 1) {
            return $defaultRuleContent;
        }

        $fields = [];
        foreach ($supportedLocales as $locale => $language) {
            $isDefault = $locale === $defaultLocale;
            $settingKey = $isDefault ? 'rules_content' : "rules_content_{$locale}";

            $fields[$locale] = [
                'language' => $language,
                'value' => AffiliateHelper::getSetting($settingKey, ''),
                'isDefault' => $isDefault,
                'settingKey' => $settingKey,
            ];
        }

        return view('plugins/affiliate-pro::settings.partials.affiliate-rules-multilingual', [
            'fields' => $fields,
            'defaultLocale' => $defaultLocale,
        ])->render();
    }
}
