<?php

namespace Botble\ProductBulkEdit\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Illuminate\Routing\Events\RouteMatched;

class ProductBulkEditServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->setNamespace('plugins/product-bulk-edit')
            ->loadHelpers();
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->setNamespace('plugins/product-bulk-edit')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        $this->app['events']->listen(RouteMatched::class, function (): void {
            DashboardMenu::default()->beforeRetrieving(function (): void {
                DashboardMenu::make()
                    ->registerItem([
                        'id' => 'cms-plugins-product-bulk-edit',
                        'priority' => 25,
                        'parent_id' => 'cms-plugins-ecommerce',
                        'name' => 'plugins/product-bulk-edit::product-bulk-edit.name',
                        'icon' => 'ti ti-table',
                        'url' => fn() => route('product-bulk-edit.index'),
                        'permissions' => ['product-bulk-edit.index'],
                    ]);
            });
        });
    }
}
