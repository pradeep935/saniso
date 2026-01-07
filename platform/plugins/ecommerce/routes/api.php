<?php

use Botble\Ecommerce\Http\Controllers\API\ProductImportController;
use Botble\Ecommerce\Http\Controllers\API\ProductController;
use Botble\Ecommerce\Http\Controllers\API\ProductCategoryController;
use Illuminate\Support\Facades\Route;

// Product API Routes
Route::group([
    'middleware' => ['api'],
    'prefix' => 'api/v1/ecommerce/',
    'namespace' => 'Botble\Ecommerce\Http\Controllers\API',
], function (): void {
    
    // Public Product API endpoints
    Route::group(['prefix' => 'products'], function (): void {
        Route::get('/', [ProductController::class, 'index'])->name('api.ecommerce.products.index');
        Route::get('/{slug}', [ProductController::class, 'show'])->name('api.ecommerce.products.show');
        Route::get('/{slug}/related', [ProductController::class, 'relatedProducts'])->name('api.ecommerce.products.related');
        Route::get('/{slug}/cross-sales', [ProductController::class, 'getCrossSaleProducts'])->name('api.ecommerce.products.cross-sales');
        Route::get('/{slug}/reviews', [ProductController::class, 'reviews'])->name('api.ecommerce.products.reviews');
        Route::get('/variation/{id}', [ProductController::class, 'getProductVariation'])->name('api.ecommerce.products.variation');
    });
    
    // Product Categories API
    Route::group(['prefix' => 'product-categories'], function (): void {
        Route::get('/', [ProductCategoryController::class, 'index'])->name('api.ecommerce.product-categories.index');
        Route::get('/{slug}', [ProductCategoryController::class, 'show'])->name('api.ecommerce.product-categories.show');
    });
    
    Route::group(['middleware' => ['auth:sanctum']], function (): void {
        // Product Import API - requires authentication
        Route::post('products/import', [ProductImportController::class, 'import'])->name('api.ecommerce.products.import');
        Route::get('products/import/progress', [ProductImportController::class, 'progress'])->name('api.ecommerce.products.import.progress');
        Route::get('products/import/template', [ProductImportController::class, 'downloadTemplate'])->name('api.ecommerce.products.import.template');
    });
});
