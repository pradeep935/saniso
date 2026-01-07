<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function () {
    Route::group([
        'namespace' => 'Botble\ProductBulkEdit\Http\Controllers',
        'prefix' => 'product-bulk-edit',
        'middleware' => ['web', 'core'],
    ], function () {
        Route::get('/', [
            'as' => 'product-bulk-edit.index',
            'uses' => 'ProductBulkEditController@index',
            'permission' => 'product-bulk-edit.index',
        ]);

        Route::get('/data', [
            'as' => 'product-bulk-edit.data',
            'uses' => 'ProductBulkEditController@getData',
            'permission' => 'product-bulk-edit.index',
        ]);

        Route::post('/update', [
            'as' => 'product-bulk-edit.update',
            'uses' => 'ProductBulkEditController@update',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::post('/update-field', [
            'as' => 'product-bulk-edit.updateField',
            'uses' => 'ProductBulkEditController@updateField',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::post('/upload-image', [
            'as' => 'product-bulk-edit.uploadImage',
            'uses' => 'ProductBulkEditController@uploadImage',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::post('/upload-gallery', [
            'as' => 'product-bulk-edit.uploadGallery',
            'uses' => 'ProductBulkEditController@uploadGallery',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::post('/upload-image-from-url', [
            'as' => 'product-bulk-edit.uploadImageFromUrl',
            'uses' => 'ProductBulkEditController@uploadImageFromUrl',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::post('/upload-gallery-from-url', [
            'as' => 'product-bulk-edit.uploadGalleryFromUrl',
            'uses' => 'ProductBulkEditController@uploadGalleryFromUrl',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::post('/delete-gallery-image', [
            'as' => 'product-bulk-edit.deleteGalleryImage',
            'uses' => 'ProductBulkEditController@deleteGalleryImage',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::get('/export', [
            'as' => 'product-bulk-edit.export',
            'uses' => 'ProductBulkEditController@export',
            'permission' => 'product-bulk-edit.index',
        ]);

        Route::post('/import', [
            'as' => 'product-bulk-edit.import',
            'uses' => 'ProductBulkEditController@import',
            'permission' => 'product-bulk-edit.update',
        ]);

        Route::delete('/delete', [
            'as' => 'product-bulk-edit.delete',
            'uses' => 'ProductBulkEditController@deleteProducts',
            'permission' => 'product-bulk-edit.update',
        ]);
    });
});
