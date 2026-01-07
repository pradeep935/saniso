<?php

namespace Botble\Mollie\Providers;

use Botble\Base\Facades\Html;
use Botble\Mollie\Forms\MolliePaymentMethodForm;
use Botble\Mollie\Services\Gateways\MolliePaymentService;
use Botble\Payment\Enums\PaymentMethodEnum;
use Botble\Payment\Facades\PaymentMethods;
use Botble\Payment\Supports\PaymentHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Mollie\Laravel\Facades\Mollie;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerMollieMethod'], 17, 2);

        $this->app->booted(function (): void {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithMollie'], 17, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 99);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['MOLLIE'] = MOLLIE_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 23, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MOLLIE_PAYMENT_METHOD_NAME) {
                $value = 'Mollie';
            }

            return $value;
        }, 23, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MOLLIE_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 23, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == MOLLIE_PAYMENT_METHOD_NAME) {
                $data = MolliePaymentService::class;
            }

            return $data;
        }, 20, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == MOLLIE_PAYMENT_METHOD_NAME) {
                try {
                    $paymentService = (new MolliePaymentService());

                    do_action('payment_before_making_api_request', MOLLIE_PAYMENT_METHOD_NAME, ['payment_id' => $payment->charge_id]);

                    $paymentDetail = $paymentService->getPaymentDetails($payment->charge_id);

                    do_action('payment_after_api_response', MOLLIE_PAYMENT_METHOD_NAME, ['payment_id' => $payment->charge_id], (array) $paymentDetail);

                    if ($paymentDetail) {
                        $data = view('plugins/mollie::detail', ['payment' => $paymentDetail])->render();
                    }
                } catch (Exception) {
                    return $data;
                }
            }

            return $data;
        }, 20, 2);

        // Add POS Terminal menu item
        add_filter('cms-dashboard-menu', function ($menu) {
            if (auth()->user() && auth()->user()->hasPermission(['orders.index'])) {
                $menu->registerItem([
                    'id' => 'cms-plugins-mollie-terminal',
                    'priority' => 10,
                    'parent_id' => 'cms-plugins-ecommerce',
                    'name' => 'POS Terminal',
                    'icon' => 'ti ti-device-tablet',
                    'url' => route('mollie.terminal.dashboard'),
                    'permissions' => ['orders.index'],
                ]);
            }
            return $menu;
        }, 1000);

        // Add Mollie Terminal to payment method enums for POS integration
        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['MOLLIE_TERMINAL'] = MOLLIE_TERMINAL_PAYMENT_METHOD_NAME;
            }
            return $values;
        }, 21, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MOLLIE_TERMINAL_PAYMENT_METHOD_NAME) {
                $value = 'Mollie Terminal';
            }
            return $value;
        }, 21, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == MOLLIE_TERMINAL_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }
            return $value;
        }, 21, 2);

        // Hook to modify POS Pro payment methods list
        add_action('pos_pro_payment_methods_list', function () {
            echo '
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                // Add Mollie Terminal to payment methods if on POS settings page
                if (window.location.href.includes("/admin/pos/settings")) {
                    const paymentMethodsContainer = document.querySelector("[name*=\"pos_pro_active_payment_methods\"]");
                    const defaultMethodSelect = document.querySelector("[name=\"pos_pro_default_payment_method\"]");
                    
                    if (paymentMethodsContainer) {
                        // Add checkbox for Mollie Terminal
                        const mollieTerminalHtml = `
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="pos_pro_active_payment_methods[]" value="mollie_terminal" id="mollie_terminal">
                                <label class="form-check-label" for="mollie_terminal">Mollie Terminal</label>
                            </div>
                        `;
                        paymentMethodsContainer.parentElement.insertAdjacentHTML("beforeend", mollieTerminalHtml);
                    }
                    
                    if (defaultMethodSelect) {
                        // Add option to default method select
                        const mollieOption = document.createElement("option");
                        mollieOption.value = "mollie_terminal";
                        mollieOption.text = "Mollie Terminal";
                        defaultMethodSelect.appendChild(mollieOption);
                    }
                }
            });
            </script>
            ';
        });
        
        // Add the hook to page footer
        add_action('admin_footer', function () {
            if (request()->is('admin/pos/settings*')) {
                do_action('pos_pro_payment_methods_list');
            }
            
            // Include Mollie POS integration JavaScript on POS pages
            if (request()->is('admin/pos*')) {
                echo '<script src="' . asset('vendor/core/plugins/mollie/js/pos-integration.js') . '?v=' . time() . '"></script>';
            }
        });
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . MolliePaymentMethodForm::create()->renderForm();
    }

    public function registerMollieMethod(?string $html, array $data): ?string
    {
        PaymentMethods::method(MOLLIE_PAYMENT_METHOD_NAME, [
            'html' => view('plugins/mollie::methods', $data)->render(),
        ]);

        return $html;
    }

    public function checkoutWithMollie(array $data, Request $request)
    {
        if ($data['type'] !== MOLLIE_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        try {
            $api = Mollie::api();

            $requestData = [
                'amount' => [
                    'currency' => $paymentData['currency'],
                    'value' => number_format((float) $paymentData['amount'], 2, '.', ''),
                ],
                'description' => $paymentData['description'],
                'redirectUrl' => PaymentHelper::getRedirectURL(),
                'webhookUrl' => route('mollie.payment.callback', $paymentData['checkout_token']),
                'metadata' => [
                    'order_id' => $paymentData['order_id'],
                    'customer_id' => $paymentData['customer_id'],
                    'customer_type' => $paymentData['customer_type'],
                ],
            ];

            do_action('payment_before_making_api_request', MOLLIE_PAYMENT_METHOD_NAME, $requestData);

            $response = $api->payments->create($requestData);

            do_action('payment_after_api_response', MOLLIE_PAYMENT_METHOD_NAME, $requestData, (array) $response);

            header('Location: ' . $response->getCheckoutUrl());
            exit;
        } catch (Exception $exception) {
            $data['error'] = true;
            $data['message'] = $exception->getMessage();
        }

        return $data;
    }
}
