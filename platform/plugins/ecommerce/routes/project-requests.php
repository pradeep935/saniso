<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\Fronts\PublicProjectController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

// Public routes
Theme::registerRoutes(function (): void {
    Route::controller(PublicProjectController::class)
        ->prefix('request-project')
        ->name('public.project-requests.')
        ->group(function (): void {
            Route::post('/', 'store')->name('store');
        });
});

// Admin routes
AdminHelper::registerRoutes(function (): void {
    Route::group(['namespace' => 'Botble\Ecommerce\Http\Controllers', 'prefix' => 'ecommerce'], function (): void {
        Route::group(['prefix' => 'project-requests', 'as' => 'project-requests.'], function (): void {
            Route::get('export', [
                'as' => 'export',
                'uses' => 'ProjectRequestController@export',
                'permission' => 'project-requests.index',
            ]);
            
            Route::get('/', [
                'as' => 'index',
                'uses' => 'ProjectRequestController@index',
                'permission' => 'project-requests.index',
            ]);
            
            Route::post('/', [
                'as' => 'index.post',
                'uses' => 'ProjectRequestController@index',
                'permission' => 'project-requests.index',
            ]);
            
            Route::get('{projectRequest}', [
                'as' => 'show',
                'uses' => 'ProjectRequestController@show',
                'permission' => 'project-requests.index',
            ]);
            
            Route::patch('{projectRequest}', [
                'as' => 'update',
                'uses' => 'ProjectRequestController@update',
                'permission' => 'project-requests.edit',
            ]);
            
            Route::delete('{projectRequest}', [
                'as' => 'destroy',
                'uses' => 'ProjectRequestController@destroy',
                'permission' => 'project-requests.delete',
            ]);
        });
    });
});