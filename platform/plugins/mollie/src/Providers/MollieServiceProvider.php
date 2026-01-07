<?php

namespace Botble\Mollie\Providers;

use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Base\Facades\DashboardMenu;
use Botble\Mollie\Services\MollieTerminalService;
use Illuminate\Support\ServiceProvider;

class MollieServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        // Define constants early
        if (! defined('MOLLIE_TERMINAL_PAYMENT_METHOD_NAME')) {
            define('MOLLIE_TERMINAL_PAYMENT_METHOD_NAME', 'mollie_terminal');
        }

        // Register the terminal service
        $this->app->singleton(MollieTerminalService::class, function ($app) {
            return new MollieTerminalService();
        });

        // Override POS Pro Settings Form with our extended version if POS Pro is active
        if (is_plugin_active('pos-pro')) {
            $this->app->bind(\Botble\PosPro\Forms\Settings\PosProSettingForm::class, function () {
                return new \Botble\Mollie\Forms\PosProExtendedSettingForm();
            });

            // Override POS Pro Checkout Controller to handle mollie_terminal payment method
            $this->app->bind(\Botble\PosPro\Http\Controllers\CheckoutController::class, function ($app) {
                return new \Botble\Mollie\Http\Controllers\ExtendedCheckoutController($app->make(\Botble\PosPro\Services\CartService::class));
            });

            // Override POS Pro Settings Controller to use our extended form and validation
            $this->app->bind(\Botble\PosPro\Http\Controllers\Settings\PosProSettingController::class, function ($app) {
                return new \Botble\Mollie\Http\Controllers\Settings\ExtendedPosProSettingController();
            });
        }
    }

    public function boot(): void
    {
        if (! is_plugin_active('payment')) {
            return;
        }

        $this->setNamespace('plugins/mollie')
            ->loadHelpers()
            ->loadRoutes()
            ->loadAndPublishViews()
            ->publishAssets();

        $this->app->booted(function (): void {
            $this->app->make('config')->set([
                'mollie.key' => get_payment_setting('api_key', MOLLIE_PAYMENT_METHOD_NAME),
            ]);

            $this->app->register(HookServiceProvider::class);
            $this->app->register(MollieHookServiceProvider::class);

            // Add Mollie Dashboard to Payment menu
            DashboardMenu::beforeRetrieving(function (): void {
                DashboardMenu::make()
                    ->registerItem([
                        'id' => 'cms-plugins-mollie-dashboard',
                        'priority' => 2,
                        'parent_id' => 'cms-plugins-payments',
                        'name' => 'Mollie Dashboard',
                        'icon' => null,
                        'url' => fn () => route('mollie.dashboard'),
                        'permissions' => ['payment.index'],
                    ]);
            });

            // Add Mollie POS integration JavaScript to POS pages
            if (is_plugin_active('pos-pro')) {
                add_filter('asset_footer_js', function ($js) {
                    if (request()->is('*pos*') || request()->is('*admin/pos*')) {
                        $js[] = 'vendor/core/plugins/mollie/js/pos-integration.js';
                        $js[] = 'vendor/core/plugins/mollie/js/pos-terminal.js';
                    }
                    return $js;
                }, 120);
            }
        });
    }
}
