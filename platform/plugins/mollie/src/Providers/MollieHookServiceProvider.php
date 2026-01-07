<?php

namespace Botble\Mollie\Providers;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\ServiceProvider;
use Botble\Payment\Enums\PaymentMethodEnum;

class MollieHookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            // Add Mollie Terminal to PaymentMethodEnum
            add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
                if ($class == PaymentMethodEnum::class) {
                    $values['MOLLIE_TERMINAL'] = MOLLIE_TERMINAL_PAYMENT_METHOD_NAME;
                }

                return $values;
            }, 21, 2);

            // Add label for Mollie Terminal
            add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
                if ($class == PaymentMethodEnum::class) {
                    if ($value == MOLLIE_TERMINAL_PAYMENT_METHOD_NAME) {
                        return 'Mollie Terminal';
                    }
                }

                return $value;
            }, 21, 2);

            // Add HTML styling for Mollie Terminal
            add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
                if ($class == PaymentMethodEnum::class) {
                    if ($value == MOLLIE_TERMINAL_PAYMENT_METHOD_NAME) {
                        return Html::tag(
                            'span',
                            PaymentMethodEnum::getLabel($value),
                            ['class' => 'label-success status-label']
                        )->toHtml();
                    }
                }

                return $value;
            }, 21, 2);

            // Hook into POS Pro checkout to handle mollie_terminal payment method mapping
            if (is_plugin_active('pos-pro')) {
                add_filter('pos_pro_map_payment_method', function ($mappedMethod, $posMethod) {
                    if ($posMethod === 'mollie_terminal') {
                        return MOLLIE_TERMINAL_PAYMENT_METHOD_NAME;
                    }
                    return $mappedMethod;
                }, 10, 2);
            }
        });
    }
}