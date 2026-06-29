<?php

use Botble\Base\Facades\AdminHelper;
use Botble\Ecommerce\Http\Controllers\AdminAjaxController;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::prefix('ajax')->name('admin.ajax.')->group(function (): void {
        Route::get('search-products', [AdminAjaxController::class, 'searchProducts'])
            ->name('search-products');

        Route::get('search-categories', [AdminAjaxController::class, 'searchCategories'])
            ->name('search-categories');

        Route::get('search-collections', [AdminAjaxController::class, 'searchCollections'])
            ->name('search-collections');
    });
});
