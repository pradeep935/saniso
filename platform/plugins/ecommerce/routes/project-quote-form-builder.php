<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'ecommerce',
    'namespace' => 'Botble\Ecommerce\Http\Controllers',
    'middleware' => ['web', 'core'],
], function () {
    Route::group(['prefix' => 'project-quote-form-builder', 'as' => 'project-quote-form-builder.'], function () {
        Route::get('/', [
            'as' => 'index',
            'uses' => 'ProjectQuoteFormBuilderController@index',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('create', [
            'as' => 'create',
            'uses' => 'ProjectQuoteFormBuilderController@create',
            'permission' => 'project-requests.index',
        ]);
        
        Route::post('/', [
            'as' => 'store',
            'uses' => 'ProjectQuoteFormBuilderController@store',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('preview', [
            'as' => 'preview',
            'uses' => 'ProjectQuoteFormBuilderController@preview',
            'permission' => 'project-requests.index',
        ]);
        
        Route::post('reorder', [
            'as' => 'reorder',
            'uses' => 'ProjectQuoteFormBuilderController@updateOrder',
            'permission' => 'project-requests.index',
        ]);
        
        Route::put('{id}', [
            'as' => 'update',
            'uses' => 'ProjectQuoteFormBuilderController@update',
            'permission' => 'project-requests.index',
        ]);
        
        Route::delete('{id}', [
            'as' => 'destroy',
            'uses' => 'ProjectQuoteFormBuilderController@destroy',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('{id}/edit', [
            'as' => 'edit',
            'uses' => 'ProjectQuoteFormBuilderController@edit',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('{id}/duplicate', [
            'as' => 'duplicate',
            'uses' => 'ProjectQuoteFormBuilderController@duplicate',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('{id}/toggle', [
            'as' => 'toggle',
            'uses' => 'ProjectQuoteFormBuilderController@toggleStatus',
            'permission' => 'project-requests.index',
        ]);
    });
});