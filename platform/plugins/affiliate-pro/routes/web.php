<?php

use Botble\AffiliatePro\Http\Controllers\LicenseController;
use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\AffiliatePro\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        Route::group(['prefix' => 'affiliates', 'as' => 'affiliate-pro.'], function (): void {
            Route::resource('', 'AffiliateController')->parameters(['' => 'affiliate'])->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            
            Route::get('view/{affiliate}', [
                'as' => 'show',
                'uses' => 'AffiliateController@show',
                'permission' => 'affiliate-pro.index',
            ]);
            
            Route::post('{affiliate}/ban', [
                'as' => 'ban',
                'uses' => 'AffiliateController@ban',
                'permission' => 'affiliate-pro.edit',
            ]);
            
            Route::post('{affiliate}/unban', [
                'as' => 'unban',
                'uses' => 'AffiliateController@unban',
                'permission' => 'affiliate-pro.edit',
            ]);
        });

        Route::group(['prefix' => 'affiliates/pending', 'as' => 'affiliate-pro.pending.'], function (): void {
            Route::match(['GET', 'POST'], '/', [
                'as' => 'index',
                'uses' => 'PendingAffiliateController@index',
                'permission' => 'affiliate-pro.edit',
            ]);

            Route::get('{id}', [
                'as' => 'show',
                'uses' => 'PendingAffiliateController@show',
                'permission' => 'affiliate-pro.edit',
            ]);

            Route::post('{id}/approve', [
                'as' => 'approve',
                'uses' => 'PendingAffiliateController@approve',
                'permission' => 'affiliate-pro.edit',
            ]);

            Route::post('{id}/reject', [
                'as' => 'reject',
                'uses' => 'PendingAffiliateController@reject',
                'permission' => 'affiliate-pro.edit',
            ]);
        });

        Route::group(['prefix' => 'affiliates/commissions', 'as' => 'affiliate-pro.commissions.'], function (): void {
            Route::resource('', 'CommissionController')
                ->parameters(['' => 'commission'])
                ->only(['index']);

            Route::get('{commission}', [
                'as' => 'show',
                'uses' => 'CommissionController@show',
                'permission' => 'affiliate.commissions.index',
            ]);

            Route::post('{commission}/approve', [
                'as' => 'approve',
                'uses' => 'CommissionController@approve',
                'permission' => 'affiliate.commissions.index',
            ]);

            Route::post('{commission}/reject', [
                'as' => 'reject',
                'uses' => 'CommissionController@reject',
                'permission' => 'affiliate.commissions.index',
            ]);
        });

        Route::group(['prefix' => 'affiliates/withdrawals', 'as' => 'affiliate-pro.withdrawals.'], function (): void {
            Route::resource('', 'WithdrawalController')
                ->parameters(['' => 'withdrawal'])
                ->only(['index']);

            Route::get('{withdrawal}', [
                'as' => 'show',
                'uses' => 'WithdrawalController@show',
                'permission' => 'affiliate.withdrawals.index',
            ]);

            Route::post('{withdrawal}/approve', [
                'as' => 'approve',
                'uses' => 'WithdrawalController@approve',
                'permission' => 'affiliate.withdrawals.index',
            ]);

            Route::post('{withdrawal}/reject', [
                'as' => 'reject',
                'uses' => 'WithdrawalController@reject',
                'permission' => 'affiliate.withdrawals.index',
            ]);
        });

        Route::group(['prefix' => 'affiliates/coupons', 'as' => 'affiliate-pro.coupons.'], function (): void {
            Route::match(['GET', 'POST'], '/', [
                'as' => 'index',
                'uses' => 'AffiliateCouponController@index',
                'permission' => 'affiliate.coupons.index',
            ]);

            Route::get('create', [
                'as' => 'create',
                'uses' => 'AffiliateCouponController@create',
                'permission' => 'affiliate.coupons.create',
            ]);

            Route::post('create', [
                'as' => 'store',
                'uses' => 'AffiliateCouponController@store',
                'permission' => 'affiliate.coupons.create',
            ]);

            Route::get('{id}', [
                'as' => 'show',
                'uses' => 'AffiliateCouponController@show',
                'permission' => 'affiliate.coupons.index',
            ]);

            Route::get('{id}/edit', [
                'as' => 'edit',
                'uses' => 'AffiliateCouponController@edit',
                'permission' => 'affiliate.coupons.edit',
            ]);

            Route::put('{id}/edit', [
                'as' => 'update',
                'uses' => 'AffiliateCouponController@update',
                'permission' => 'affiliate.coupons.edit',
            ]);

            Route::delete('{id}', [
                'as' => 'destroy',
                'uses' => 'AffiliateCouponController@destroy',
                'permission' => 'affiliate.coupons.destroy',
            ]);

            Route::post('bulk-actions', [
                'as' => 'bulk-actions',
                'uses' => 'AffiliateCouponController@bulkActions',
                'permission' => 'affiliate.coupons.destroy',
            ]);
        });

        Route::group(['prefix' => 'affiliates/short-links', 'as' => 'affiliate-pro.short-links.'], function (): void {
            Route::match(['GET', 'POST'], '/', [
                'as' => 'index',
                'uses' => 'AffiliateShortLinkController@index',
                'permission' => 'affiliate.short-links.index',
            ]);

            Route::get('create', [
                'as' => 'create',
                'uses' => 'AffiliateShortLinkController@create',
                'permission' => 'affiliate.short-links.create',
            ]);

            Route::post('create', [
                'as' => 'store',
                'uses' => 'AffiliateShortLinkController@store',
                'permission' => 'affiliate.short-links.create',
            ]);

            Route::get('{short_link}', [
                'as' => 'show',
                'uses' => 'AffiliateShortLinkController@show',
                'permission' => 'affiliate.short-links.index',
            ]);

            Route::get('{short_link}/edit', [
                'as' => 'edit',
                'uses' => 'AffiliateShortLinkController@edit',
                'permission' => 'affiliate.short-links.edit',
            ]);

            Route::put('{short_link}/edit', [
                'as' => 'update',
                'uses' => 'AffiliateShortLinkController@update',
                'permission' => 'affiliate.short-links.edit',
            ]);

            Route::delete('{short_link}', [
                'as' => 'destroy',
                'uses' => 'AffiliateShortLinkController@destroy',
                'permission' => 'affiliate.short-links.destroy',
            ]);
        });

        Route::group(['prefix' => 'affiliates/settings', 'as' => 'affiliate-pro.'], function (): void {
            Route::get('/', [
                'as' => 'settings',
                'uses' => 'Settings\AffiliateSettingController@index',
                'permission' => 'affiliate.settings',
            ]);

            Route::put('/', [
                'as' => 'settings.update',
                'uses' => 'Settings\AffiliateSettingController@update',
                'permission' => 'affiliate.settings',
            ]);
        });

        Route::group([
            'prefix' => 'affiliates/reports',
            'as' => 'affiliate-pro.reports.',
            'permission' => 'affiliate.reports',
        ], function (): void {
            Route::get('', [
                'as' => 'index',
                'uses' => 'ReportController@index',
            ]);

            Route::post('top-affiliates', [
                'as' => 'top-affiliates',
                'uses' => 'ReportController@getTopAffiliates',
                'permission' => 'affiliate.reports',
            ]);

            Route::post('recent-commissions', [
                'as' => 'recent-commissions',
                'uses' => 'ReportController@getRecentCommissions',
                'permission' => 'affiliate.reports',
            ]);

            Route::post('recent-withdrawals', [
                'as' => 'recent-withdrawals',
                'uses' => 'ReportController@getRecentWithdrawals',
                'permission' => 'affiliate.reports',
            ]);

            Route::get('dashboard-widget-general', [
                'as' => 'dashboard-widget.general',
                'uses' => 'ReportController@getDashboardWidgetGeneral',
                'permission' => 'affiliate.reports',
            ]);

            // Enhanced reports API endpoints
            Route::get('geographic-data', [
                'as' => 'geographic-data',
                'uses' => 'ReportController@getGeographicData',
                'permission' => 'affiliate.reports',
            ]);

            Route::get('short-link-performance', [
                'as' => 'short-link-performance',
                'uses' => 'ReportController@getShortLinkPerformance',
                'permission' => 'affiliate.reports',
            ]);

            Route::get('commission-data', [
                'as' => 'commission-data',
                'uses' => 'ReportController@getCommissionData',
                'permission' => 'affiliate.reports',
            ]);
        });

        Route::group(['prefix' => 'affiliates/license', 'as' => 'affiliate-pro.license.'], function (): void {
            Route::get('/', [LicenseController::class, 'index'])->name('index');
            Route::post('activate', [LicenseController::class, 'activate'])
                ->name('activate')
                ->middleware('preventDemo');
            Route::post('deactivate', [LicenseController::class, 'deactivate'])
                ->name('deactivate')
                ->middleware('preventDemo');
        });
    });

    Theme::registerRoutes(function () {
        Route::get('go/{shortCode}', 'ShortLinkController@redirect')->name('affiliate-pro.short-link.redirect');

        Route::middleware('customer')
            ->namespace('Customers')
            ->name('affiliate-pro.')
            ->group(function (): void {
                Route::prefix('customer/affiliate')->group(function (): void {
                    Route::get('/', [
                        'as' => 'register',
                        'uses' => 'PublicController@showRegisterForm',
                    ]);

                    Route::post('/', [
                        'as' => 'register.post',
                        'uses' => 'PublicController@register',
                    ]);
                    
                    Route::get('/banned', [
                        'as' => 'banned',
                        'uses' => 'PublicController@banned',
                    ]);
                    
                    Route::middleware(\Botble\AffiliatePro\Http\Middleware\CheckAffiliateBanned::class)
                        ->group(function (): void {

                    Route::get('/dashboard', [
                        'as' => 'dashboard',
                        'uses' => 'PublicController@dashboard',
                    ]);

                    Route::get('/commissions', [
                        'as' => 'commissions',
                        'uses' => 'PublicController@commissions',
                    ]);

                    Route::get('/withdrawals', [
                        'as' => 'withdrawals',
                        'uses' => 'PublicController@withdrawals',
                    ]);

                    Route::post('/withdrawals', [
                        'as' => 'withdrawals.store',
                        'uses' => 'PublicController@storeWithdrawal',
                    ]);

                    // Ajax routes
                    Route::get('/ajax/search-products', [
                        'as' => 'ajax.search-products',
                        'uses' => 'PublicController@ajaxSearchProducts',
                    ]);

                    Route::get('/materials', [
                        'as' => 'materials',
                        'uses' => 'PublicController@materials',
                    ]);

                    Route::get('/reports', [
                        'as' => 'reports',
                        'uses' => 'PublicController@reports',
                    ]);

                    // Coupon management (view only)
                    Route::get('/coupons', [
                        'as' => 'coupons',
                        'uses' => 'PublicController@coupons',
                    ]);

                    // Short link management
                    Route::get('/short-links', [
                        'as' => 'short-links',
                        'uses' => 'ShortLinkController@index',
                    ]);

                    Route::post('/short-links', [
                        'as' => 'short-links.store',
                        'uses' => 'ShortLinkController@store',
                    ]);

                    Route::delete('/short-links/{id}', [
                        'as' => 'short-links.destroy',
                        'uses' => 'ShortLinkController@destroy',
                    ]);
                    }); // End of banned check middleware
                });
            });
    });
});
