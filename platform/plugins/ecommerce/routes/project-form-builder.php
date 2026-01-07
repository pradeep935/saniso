<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'ecommerce',
    'namespace' => 'Botble\Ecommerce\Http\Controllers',
    'middleware' => ['web', 'core'],
], function () {
    Route::group(['prefix' => 'project-form-builder', 'as' => 'project-form-builder.'], function () {
        Route::get('/', [
            'as' => 'index',
            'uses' => 'ProjectFormBuilderController@index',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('create', [
            'as' => 'create',
            'uses' => 'ProjectFormBuilderController@create',
            'permission' => 'project-requests.index',
        ]);
        
        Route::post('/', [
            'as' => 'store',
            'uses' => 'ProjectFormBuilderController@store',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('{id}/edit', [
            'as' => 'edit',
            'uses' => 'ProjectFormBuilderController@edit',
            'permission' => 'project-requests.index',
        ]);
        
        Route::put('{id}', [
            'as' => 'update',
            'uses' => 'ProjectFormBuilderController@update',
            'permission' => 'project-requests.index',
        ]);
        
        Route::delete('{id}', [
            'as' => 'destroy',
            'uses' => 'ProjectFormBuilderController@destroy',
            'permission' => 'project-requests.index',
        ]);
        
        Route::post('reorder', [
            'as' => 'reorder',
            'uses' => 'ProjectFormBuilderController@reorder',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('{id}/toggle', [
            'as' => 'toggle',
            'uses' => 'ProjectFormBuilderController@toggle',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('{id}/duplicate', [
            'as' => 'duplicate',
            'uses' => 'ProjectFormBuilderController@duplicate',
            'permission' => 'project-requests.index',
        ]);
        
        Route::get('preview', [
            'as' => 'preview',
            'uses' => 'ProjectFormBuilderController@preview',
            'permission' => 'project-requests.index',
        ]);
    });
});