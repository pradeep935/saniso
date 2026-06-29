<?php

use App\Http\Controllers\OfflineController;
use Illuminate\Support\Facades\Route;

// Offline page route used by service worker and fallback
Route::get('/offline', OfflineController::class);

// Admin routes for duplicate products
Route::group(['prefix' => 'admin', 'middleware' => ['web', 'auth']], function () {
    Route::get('/duplicate-products', [App\Http\Controllers\Admin\DuplicateProductController::class, 'index'])
        ->name('admin.duplicate-products');
    Route::post('/duplicate-products/remove', [App\Http\Controllers\Admin\DuplicateProductController::class, 'remove'])
        ->name('admin.duplicate-products.remove');
    Route::post('/duplicate-products/bulk-remove', [App\Http\Controllers\Admin\DuplicateProductController::class, 'bulkRemove'])
        ->name('admin.duplicate-products.bulk-remove');
});
