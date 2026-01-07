<?php

use Botble\AIContentGenerator\Http\Controllers\AIContentController;
use Botble\AIContentGenerator\Http\Controllers\SettingsController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Botble\AIContentGenerator\Http\Controllers'], function (): void {
    AdminHelper::registerRoutes(function (): void {
        // AI Content generation routes
        Route::group(['prefix' => 'ai-content-generator', 'as' => 'ai-content-generator.'], function (): void {
            Route::post('generate', [
                'as' => 'generate',
                'uses' => 'AIContentController@generate',
                'permission' => 'ai-content-generator.access',
            ]);
        });
        
        // AI Content generation routes (legacy)
        Route::group(['prefix' => 'ai-content', 'as' => 'ai-content.'], function (): void {
            Route::post('generate-product', [
                'as' => 'generate-product',
                'uses' => 'AIContentController@generateProductContent',
                'permission' => 'ai-content-generator.access',
            ]);
            
            Route::post('generate-blog', [
                'as' => 'generate-blog',
                'uses' => 'AIContentController@generateBlogContent',
                'permission' => 'ai-content-generator.access',
            ]);
            
            Route::post('translate', [
                'as' => 'translate',
                'uses' => 'AIContentController@translateContent',
                'permission' => 'ai-content-generator.access',
            ]);

            Route::get('status', [
                'as' => 'status',
                'uses' => 'AIContentController@checkStatus',
                'permission' => 'ai-content-generator.access',
            ]);
        });

        // Settings routes
        Route::group(['prefix' => 'settings', 'as' => 'ai-content-generator.'], function (): void {
            Route::get('ai-content-generator', [
                'as' => 'settings',
                'uses' => 'SettingsController@index',
                'permission' => 'ai-content-generator.settings',
            ]);
            
            Route::post('ai-content-generator', [
                'as' => 'settings.update',
                'uses' => 'SettingsController@update',
                'permission' => 'ai-content-generator.settings',
            ]);
        });
    });
});
