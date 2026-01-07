<?php

namespace Botble\Mollie\Forms;

use Botble\Base\Forms\FieldOptions\MultiChecklistFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Mollie\Http\Requests\PosProExtendedSettingRequest;
use Botble\Setting\Forms\SettingForm;

class PosProExtendedSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $activePaymentMethods = $this->getSettingValue('pos_pro_active_payment_methods');

        if ($activePaymentMethods) {
            $activePaymentMethods = json_decode($activePaymentMethods, true);
        } else {
            $activePaymentMethods = ['cash', 'card', 'other'];
        }

        $this
            ->setSectionTitle(trans('plugins/pos-pro::pos.settings.title'))
            ->setSectionDescription(trans('plugins/pos-pro::pos.settings.description'))
            ->setValidatorClass(PosProExtendedSettingRequest::class)
            ->add(
                'pos_pro_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.enable'))
                    ->value($this->getSettingValue('pos_pro_enabled', true))
                    ->attributes([
                        'data-bb-toggle' => 'collapse',
                        'data-bb-target' => '#pos-pro-settings',
                    ])
            )
            ->add('open_wrapper', HtmlField::class, [
                'html' => sprintf(
                    '<div id="pos-pro-settings" style="display: %s">',
                    $this->getSettingValue('pos_pro_enabled', true) ? 'block' : 'none'
                ),
            ])
            ->add(
                'pos_pro_active_payment_methods[]',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.active_payment_methods'))
                    ->choices([
                        'cash' => trans('plugins/pos-pro::pos.cash'),
                        'card' => trans('plugins/pos-pro::pos.card'),
                        'mollie_terminal' => 'Mollie Terminal',
                        'other' => trans('plugins/pos-pro::pos.other'),
                    ])
                    ->selected($activePaymentMethods)
                    ->helperText(trans('plugins/pos-pro::pos.settings.active_payment_methods_helper') . ' Mollie Terminal requires valid Mollie API configuration.')
            )
            ->add(
                'pos_pro_default_payment_method',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.default_payment_method'))
                    ->choices([
                        'cash' => trans('plugins/pos-pro::pos.cash'),
                        'card' => trans('plugins/pos-pro::pos.card'),
                        'mollie_terminal' => 'Mollie Terminal',
                        'other' => trans('plugins/pos-pro::pos.other'),
                    ])
                    ->selected($this->getSettingValue('pos_pro_default_payment_method', 'cash'))
                    ->helperText(trans('plugins/pos-pro::pos.settings.default_payment_method_helper'))
            )
            ->add(
                'pos_pro_auto_apply_discount',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.auto_apply_discount'))
                    ->value($this->getSettingValue('pos_pro_auto_apply_discount', false))
                    ->helperText(trans('plugins/pos-pro::pos.settings.auto_apply_discount_helper'))
            )
            ->add(
                'pos_pro_auto_add_shipping',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.auto_add_shipping'))
                    ->value($this->getSettingValue('pos_pro_auto_add_shipping', false))
                    ->helperText(trans('plugins/pos-pro::pos.settings.auto_add_shipping_helper'))
            )
            ->add(
                'pos_pro_default_shipping_amount',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.default_shipping_amount'))
                    ->value($this->getSettingValue('pos_pro_default_shipping_amount', 0))
                    ->helperText(trans('plugins/pos-pro::pos.settings.default_shipping_amount_helper'))
            )
            ->add(
                'pos_pro_remember_customer_selection',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.remember_customer_selection'))
                    ->value($this->getSettingValue('pos_pro_remember_customer_selection', true))
                    ->helperText(trans('plugins/pos-pro::pos.settings.remember_customer_selection_helper'))
            )
            ->add(
                'pos_pro_print_receipt',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/pos-pro::pos.settings.print_receipt'))
                    ->value($this->getSettingValue('pos_pro_print_receipt', true))
                    ->helperText(trans('plugins/pos-pro::pos.settings.print_receipt_helper'))
            )
            ->when(is_plugin_active('marketplace'), function ($form) {
                $form->add(
                    'pos_pro_separate_vendor_orders',
                    OnOffCheckboxField::class,
                    OnOffFieldOption::make()
                        ->label(trans('plugins/pos-pro::pos.settings.separate_vendor_orders'))
                        ->value($this->getSettingValue('pos_pro_separate_vendor_orders', true))
                        ->helperText(trans('plugins/pos-pro::pos.settings.separate_vendor_orders_helper'))
                );
            })
            ->add('close_wrapper', HtmlField::class, [
                'html' => '</div>',
            ]);
    }

    protected function getSettingValue(string $key, $default = null)
    {
        return setting($key, $default);
    }
}