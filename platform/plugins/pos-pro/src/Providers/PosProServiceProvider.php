<?php

namespace Botble\PosPro\Providers;

use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider as BaseServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;
use Botble\PosPro\Facades\PosProHelper;
use Botble\PosPro\Http\Middleware\PosLocaleMiddleware;
use Botble\PosPro\Support\PosProHelper as PosProHelperSupport;
use Illuminate\Foundation\AliasLoader;

class PosProServiceProvider extends BaseServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->app->register(CommandServiceProvider::class);
        $this->app->register(HookServiceProvider::class);
        $this->app->register(FormServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        $this->app->singleton('pos-pro.helper', function () {
            return new PosProHelperSupport();
        });

        AliasLoader::getInstance()->alias('PosProHelper', PosProHelper::class);
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->setNamespace('plugins/pos-pro')
            ->loadAndPublishConfigurations(['permissions'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->loadRoutes()
            ->loadHelpers()
            ->publishAssets();

        $router = $this->app['router'];
        $router->aliasMiddleware('pos-locale', PosLocaleMiddleware::class);

        PanelSectionManager::beforeRendering(function (): void {
            PanelSectionManager::default()
                ->registerItem(
                    SettingEcommercePanelSection::class,
                    fn () => PanelSectionItem::make('settings.ecommerce.pos')
                        ->setTitle(trans('plugins/pos-pro::pos.settings.title'))
                        ->withIcon('ti ti-cash-register')
                        ->withDescription(trans('plugins/pos-pro::pos.settings.description'))
                        ->withPriority(195)
                        ->withRoute('pos-pro.settings.edit')
                );
        });

        $this->app->booted(function (): void {
            DashboardMenu::default()->beforeRetrieving(function (): void {
                DashboardMenu::make()
                    ->registerItem([
                        'id' => 'cms-plugins-pos-pro',
                        'priority' => 5,
                        'parent_id' => null,
                        'name' => 'plugins/pos-pro::pos.name',
                        'icon' => 'ti ti-cash-register',
                        'url' => fn () => route('pos-pro.index'),
                        'permissions' => ['pos.index'],
                    ])
                    ->registerItem([
                        'id' => 'cms-plugins-pos-pro-pos',
                        'priority' => 0,
                        'parent_id' => 'cms-plugins-pos-pro',
                        'name' => 'plugins/pos-pro::pos.pos',
                        'icon' => 'ti ti-devices',
                        'url' => fn () => route('pos-pro.index'),
                        'permissions' => ['pos.index'],
                    ])
                    ->registerItem([
                        'id' => 'cms-plugins-pos-pro-devices',
                        'priority' => 1,
                        'parent_id' => 'cms-plugins-pos-pro',
                        'name' => 'plugins/pos-pro::pos.device_management.title',
                        'icon' => 'ti ti-router',
                        'url' => fn () => route('pos-devices.index'),
                        'permissions' => ['pos.devices'],
                    ])
                    ->registerItem([
                        'id' => 'cms-plugins-pos-pro-reports',
                        'priority' => 1,
                        'parent_id' => 'cms-plugins-pos-pro',
                        'name' => 'plugins/pos-pro::pos.reports.title',
                        'icon' => 'ti ti-chart-bar',
                        'url' => fn () => route('pos-pro.reports.index'),
                        'permissions' => ['pos.reports'],
                    ])
                    ->registerItem([
                        'id' => 'cms-plugins-pos-pro-settings',
                        'priority' => 2,
                        'parent_id' => 'cms-plugins-pos-pro',
                        'name' => 'plugins/pos-pro::pos.settings.title',
                        'icon' => 'ti ti-settings',
                        'url' => fn () => route('pos-pro.settings.edit'),
                        'permissions' => ['pos.settings'],
                    ])
                    ->registerItem([
                        'id' => 'cms-plugins-pos-pro-license',
                        'priority' => 3,
                        'parent_id' => 'cms-plugins-pos-pro',
                        'name' => 'plugins/pos-pro::pos.license.title',
                        'icon' => 'ti ti-key',
                        'url' => fn () => route('pos-pro.license.index'),
                        'permissions' => [ACL_ROLE_SUPER_USER],
                    ]);
            });
        });
    }
}
