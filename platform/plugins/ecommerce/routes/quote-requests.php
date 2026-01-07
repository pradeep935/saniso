<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\Fronts\PublicQuoteController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

// Public routes
Theme::registerRoutes(function (): void {
    Route::controller(PublicQuoteController::class)
        ->prefix('request-quote')
        ->name('public.quote-requests.')
        ->group(function (): void {
            Route::post('/', 'store')->name('store');
        });
});

// Customer routes
Theme::registerRoutes(function (): void {
    Route::group(['prefix' => 'customer', 'middleware' => ['customer']], function (): void {
        Route::group(['prefix' => 'quote-requests', 'as' => 'customer.quote-requests.'], function (): void {
            Route::get('/', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@index',
                'as' => 'index'
            ]);
            
            Route::get('{id}', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@show',
                'as' => 'show'
            ]);
            
            Route::patch('{id}/status', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@updateStatus',
                'as' => 'update-status'
            ]);
            
            Route::post('{id}/messages', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@sendMessage',
                'as' => 'send-message'
            ]);
            
            Route::get('{id}/messages', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@getMessages',
                'as' => 'get-messages'
            ]);
            
            Route::get('notifications', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@getNotifications',
                'as' => 'notifications'
            ]);
            
            Route::patch('notifications/{id}/read', [
                'uses' => 'Botble\Ecommerce\Http\Controllers\Customers\QuoteRequestController@markNotificationAsRead',
                'as' => 'notifications.read'
            ]);
        });
    });
});

// Admin routes
AdminHelper::registerRoutes(function (): void {
    Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'prefix' => 'ecommerce'], function (): void {
        Route::group(['prefix' => 'quote-requests', 'as' => 'quote-requests.'], function (): void {
            Route::get('settings', [
                'as' => 'settings',
                'uses' => 'QuoteRequestController@settings',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::post('settings', [
                'as' => 'settings.update',
                'uses' => 'QuoteRequestController@updateSettings',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('export', [
                'as' => 'export',
                'uses' => 'QuoteRequestController@export',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('/', [
                'as' => 'index',
                'uses' => 'QuoteRequestController@index',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('{quoteRequest}', [
                'as' => 'show',
                'uses' => 'QuoteRequestController@show',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::patch('{quoteRequest}', [
                'as' => 'update',
                'uses' => 'QuoteRequestController@update',
                'permission' => 'quote-requests.edit',
            ]);
            
            Route::delete('{quoteRequest}', [
                'as' => 'destroy',
                'uses' => 'QuoteRequestController@destroy',
                'permission' => 'quote-requests.delete',
            ]);
        });

        // Quote Form Builder routes
        Route::group(['prefix' => 'quote-form-builder', 'as' => 'admin.ecommerce.quote-form-builder.'], function (): void {
            Route::get('/', [
                'as' => 'index',
                'uses' => 'QuoteFormBuilderController@index',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('create', [
                'as' => 'create',
                'uses' => 'QuoteFormBuilderController@create',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::post('/', [
                'as' => 'store',
                'uses' => 'QuoteFormBuilderController@store',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('{id}/edit', [
                'as' => 'edit',
                'uses' => 'QuoteFormBuilderController@edit',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::put('{id}', [
                'as' => 'update',
                'uses' => 'QuoteFormBuilderController@update',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::delete('{id}', [
                'as' => 'destroy',
                'uses' => 'QuoteFormBuilderController@destroy',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::post('reorder', [
                'as' => 'reorder',
                'uses' => 'QuoteFormBuilderController@reorder',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('{id}/duplicate', [
                'as' => 'duplicate',
                'uses' => 'QuoteFormBuilderController@duplicate',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('{id}/toggle-status', [
                'as' => 'toggle-status',
                'uses' => 'QuoteFormBuilderController@toggleStatus',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::post('update-order', [
                'as' => 'update-order',
                'uses' => 'QuoteFormBuilderController@updateOrder',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('styles', [
                'as' => 'styles',
                'uses' => 'QuoteFormBuilderController@styles',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::post('styles', [
                'as' => 'styles.update',
                'uses' => 'QuoteFormBuilderController@updateStyles',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::get('preview', [
                'as' => 'preview',
                'uses' => 'QuoteFormBuilderController@preview',
                'permission' => 'quote-requests.index',
            ]);
        });
        
        // Quote notification routes for real-time updates
        Route::group(['prefix' => 'quote-notifications', 'as' => 'quote-notifications.'], function (): void {
            Route::get('latest', [
                'as' => 'latest',
                'uses' => 'QuoteNotificationController@getLatest',
                'permission' => 'quote-requests.index',
            ]);
            
            Route::post('{id}/mark-read', [
                'as' => 'mark-read',
                'uses' => 'QuoteNotificationController@markRead',
                'permission' => 'quote-requests.index',
            ]);
        });
    });
});