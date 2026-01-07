<?php

namespace Platform\InStoreProductScanner\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Platform\InStoreProductScanner\Services\ProductLookupService;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/scanner.php', 'scanner');

        $this->app->singleton(ProductLookupService::class, function ($app) {
            return new ProductLookupService();
        });
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        // Load admin routes if present
        if (file_exists(__DIR__ . '/../../routes/admin.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/admin.php');
        }
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'in-store-product-scanner');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->publishes([
            __DIR__ . '/../../config/scanner.php' => config_path('scanner.php'),
        ], 'config');
        // Publish front-end assets to public folder so blade can reference them
        $this->publishes([
            __DIR__ . '/../../resources/assets/css' => public_path('vendor/plugins/in-store-product-scanner/css'),
            __DIR__ . '/../../resources/assets/js' => public_path('vendor/plugins/in-store-product-scanner/js'),
        ], 'assets');
    }
}
