<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['api', 'auth:sanctum'],
    'prefix' => 'api/v1/multi-branch-inventory',
    'as' => 'api.multi-branch-inventory.',
], function () {

    // Branch API routes
    Route::get('branches', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'apiIndex']);
    Route::get('branches/{id}', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'apiShow']);
    
    // Inventory API routes  
    Route::get('inventory/{branch_id}', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'apiBranchInventory']);
    Route::post('inventory/sync', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'apiSyncInventory']);
    
    // Stock Transfer API routes
    Route::get('transfers', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'apiIndex']);
    Route::post('transfers', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'apiStore']);
    Route::get('transfers/{id}', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'apiShow']);
    
    // Quick stock check API
    Route::get('stock-check/{product_id}', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'apiQuickStockCheck']);
    Route::get('low-stock-alerts', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'apiLowStockAlerts']);

});