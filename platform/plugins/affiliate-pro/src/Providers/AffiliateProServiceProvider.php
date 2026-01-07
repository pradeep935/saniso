<?php

namespace Botble\AffiliatePro\Providers;

use Botble\AffiliatePro\Facades\AffiliateHelper;
use Botble\AffiliatePro\Http\Middleware\AffiliateTrackingMiddleware;
use Botble\AffiliatePro\Services\AffiliateTrackingService;
use Botble\AffiliatePro\Services\LinkShorteningService;
use Botble\AffiliatePro\Supports\AffiliateHelper as AffiliateHelperSupport;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider as BaseServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Ecommerce\PanelSections\SettingEcommercePanelSection;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Foundation\AliasLoader;

class AffiliateProServiceProvider extends BaseServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this->app->register(EventServiceProvider::class);
        $this->app->register(WidgetServiceProvider::class);

        $this->app->singleton('affiliate-helper', function () {
            return new AffiliateHelperSupport();
        });

        $this->app->singleton(AffiliateTrackingService::class);
        $this->app->singleton(LinkShorteningService::class);

        AliasLoader::getInstance()->alias('AffiliateHelper', AffiliateHelper::class);
    }

    public function boot(): void
    {
        if (! is_plugin_active('ecommerce')) {
            return;
        }

        $this
            ->setNamespace('plugins/affiliate-pro')
            ->loadAndPublishConfigurations(['permissions', 'email'])
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadRoutes()
            ->loadMigrations()
            ->loadHelpers()
            ->publishAssets();

        $this->app->booted(function () {
            Assets::addScriptsDirectly([
                'vendor/core/plugins/affiliate-pro/js/affiliate-actions.js',
                'vendor/core/plugins/affiliate-pro/js/affiliate-ban-actions.js',
            ]);

            ThemeSupport::registerToastNotification();

            $this->app->register(HookServiceProvider::class);

            $emailConfig = config('plugins.affiliate-pro.email', []);
            EmailHandler::addTemplateSettings(AFFILIATE_PRO_MODULE_SCREEN_NAME, $emailConfig);

            // Register front-end assets for customer pages
            add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form) {
                if (auth('customer')->check() &&
                    request()->segment(1) === 'account' &&
                    request()->segment(2) === 'affiliate') {
                    app('affiliate-helper')->registerAssets();
                }

                return $form;
            }, 127);
        });

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('affiliate.tracking', AffiliateTrackingMiddleware::class);
        $router->pushMiddlewareToGroup('web', AffiliateTrackingMiddleware::class);

        PanelSectionManager::beforeRendering(function (): void {
            PanelSectionManager::default()
                ->registerItem(
                    SettingEcommercePanelSection::class,
                    fn () => PanelSectionItem::make('affiliate-pro')
                        ->setTitle(trans('plugins/affiliate-pro::settings.title'))
                        ->withIcon('ti ti-share')
                        ->withDescription(trans('plugins/affiliate-pro::settings.description'))
                        ->withPriority(200)
                        ->withRoute('affiliate-pro.settings')
                );
        });

        DashboardMenu::default()->beforeRetrieving(function (): void {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro',
                    'priority' => 5,
                    'parent_id' => null,
                    'name' => 'plugins/affiliate-pro::affiliate.name',
                    'icon' => 'ti ti-share',
                    'url' => fn () => route('affiliate-pro.index'),
                    'permissions' => ['affiliate.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-all',
                    'priority' => 1,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::affiliate.all',
                    'icon' => 'ti ti-users',
                    'url' => fn () => route('affiliate-pro.index'),
                    'permissions' => ['affiliate.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-pending',
                    'priority' => 2,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::affiliate.pending_requests',
                    'icon' => 'ti ti-user-check',
                    'url' => fn () => route('affiliate-pro.pending.index'),
                    'permissions' => ['affiliate-pro.edit'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-commissions',
                    'priority' => 3,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::commission.name',
                    'icon' => 'ti ti-coin',
                    'url' => fn () => route('affiliate-pro.commissions.index'),
                    'permissions' => ['affiliate.commissions.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-withdrawals',
                    'priority' => 4,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::withdrawal.name',
                    'icon' => 'ti ti-cash',
                    'url' => fn () => route('affiliate-pro.withdrawals.index'),
                    'permissions' => ['affiliate.withdrawals.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-reports',
                    'priority' => 5,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::reports.name',
                    'icon' => 'ti ti-chart-bar',
                    'url' => fn () => route('affiliate-pro.reports.index'),
                    'permissions' => ['affiliate.reports'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-coupons',
                    'priority' => 6,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::coupon.name',
                    'icon' => 'ti ti-ticket',
                    'url' => fn () => route('affiliate-pro.coupons.index'),
                    'permissions' => ['affiliate.coupons.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-short-links',
                    'priority' => 7,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::short-link.name',
                    'icon' => 'ti ti-link',
                    'url' => fn () => route('affiliate-pro.short-links.index'),
                    'permissions' => ['affiliate.short-links.index'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-settings',
                    'priority' => 8,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::settings.name',
                    'icon' => 'ti ti-settings',
                    'url' => fn () => route('affiliate-pro.settings'),
                    'permissions' => ['affiliate.settings'],
                ])
                ->registerItem([
                    'id' => 'cms-plugins-affiliate-pro-license',
                    'priority' => 9,
                    'parent_id' => 'cms-plugins-affiliate-pro',
                    'name' => 'plugins/affiliate-pro::affiliate.license.title',
                    'icon' => 'ti ti-key',
                    'url' => fn () => route('affiliate-pro.license.index'),
                    'permissions' => ['affiliate.index'],
                ]);
        });

        // Register customer dashboard menu items
        DashboardMenu::for('customer')->beforeRetrieving(function (): void {
            if (AffiliateHelper::isRegistrationEnabled()) {
                $customer = auth('customer')->user();

                if ($customer) {
                    $affiliate = app('affiliate-helper')->getAffiliateByCustomerId($customer->id);

                    if ($affiliate) {
                        // If customer is already an affiliate, show affiliate dashboard menu
                        DashboardMenu::make()
                            ->registerItem([
                                'id' => 'cms-customer-affiliate',
                                'priority' => 80,
                                'name' => trans('plugins/affiliate-pro::affiliate.program'),
                                'url' => fn () => route('affiliate-pro.dashboard'),
                                'icon' => 'ti ti-share',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-dashboard',
                                'priority' => 1,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::affiliate.dashboard'),
                                'url' => fn () => route('affiliate-pro.dashboard'),
                                'icon' => 'ti ti-dashboard',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-commissions',
                                'priority' => 2,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::commission.history'),
                                'url' => fn () => route('affiliate-pro.commissions'),
                                'icon' => 'ti ti-coin',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-withdrawals',
                                'priority' => 3,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::withdrawal.request'),
                                'url' => fn () => route('affiliate-pro.withdrawals'),
                                'icon' => 'ti ti-cash',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-materials',
                                'priority' => 4,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::affiliate.promotional_materials'),
                                'url' => fn () => route('affiliate-pro.materials'),
                                'icon' => 'ti ti-photo',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-reports',
                                'priority' => 5,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::affiliate.detailed_reports'),
                                'url' => fn () => route('affiliate-pro.reports'),
                                'icon' => 'ti ti-chart-pie',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-coupons',
                                'priority' => 6,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::affiliate.affiliate_coupons'),
                                'url' => fn () => route('affiliate-pro.coupons'),
                                'icon' => 'ti ti-ticket',
                            ])
                            ->registerItem([
                                'id' => 'cms-customer-affiliate-short-links',
                                'priority' => 7,
                                'parent_id' => 'cms-customer-affiliate',
                                'name' => trans('plugins/affiliate-pro::affiliate.short_links'),
                                'url' => fn () => route('affiliate-pro.short-links'),
                                'icon' => 'ti ti-link',
                            ]);
                    } else {
                        // If customer is not an affiliate, show registration link
                        DashboardMenu::make()
                            ->registerItem([
                                'id' => 'cms-customer-affiliate',
                                'priority' => 80,
                                'name' => trans('plugins/affiliate-pro::affiliate.program'),
                                'url' => fn () => route('affiliate-pro.register'),
                                'icon' => 'ti ti-share',
                            ]);
                    }
                }
            }
        });
    }
}
