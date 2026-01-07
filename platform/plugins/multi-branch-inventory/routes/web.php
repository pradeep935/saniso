<?php

use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;
use Botble\MultiBranchInventory\Models\Branch;
use Botble\MultiBranchInventory\Models\BranchInventory;
use Botble\MultiBranchInventory\Models\StockTransfer;
use Botble\MultiBranchInventory\Models\IncomingGood;

AdminHelper::registerRoutes(function () {
    
    // Register route model bindings
    Route::model('branch', Branch::class);
    Route::model('branchInventory', BranchInventory::class);  
    Route::model('stockTransfer', StockTransfer::class);
    Route::model('incomingGood', IncomingGood::class);
    
    // Branch Management
    Route::group(['prefix' => 'branches', 'as' => 'branches.'], function () {
        Route::get('/', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'index'])->name('index');
        Route::get('/create', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'create'])->name('create');
        Route::post('/', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'store'])->name('store');
        Route::get('/{branch}', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'show'])->name('show');
        Route::get('/{branch}/edit', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'edit'])->name('edit');
        Route::put('/{branch}', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'destroy'])->name('destroy');
        Route::post('/{branch}/test-connection', [\Botble\MultiBranchInventory\Http\Controllers\BranchController::class, 'testConnection'])->name('test-connection');
    });

    // Branch Inventory
    Route::group(['prefix' => 'branch-inventory', 'as' => 'branch-inventory.'], function () {
        Route::get('/', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'inventoryIndex'])->name('index');
        Route::post('/', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'store'])->name('store');
        Route::get('/old', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'index'])->name('old-index');
        Route::get('/adjust-stock-form', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'adjustStockForm'])->name('adjust-stock-form');
        Route::get('/{branchInventory}', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'show'])->name('show');
        Route::get('/{branchInventory}/edit', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'edit'])->name('edit');
        Route::get('/{id}/details', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'getDetails'])->name('details');
        Route::put('/{branchInventory}', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'update'])->name('update');
        Route::post('/{branchInventory}/adjust-stock', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'adjustStock'])->name('adjust-stock');
        
        // AJAX endpoints for simple inventory management
        Route::post('/update-quantity', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'updateQuantity'])->name('update-quantity');
        Route::get('/branch-products', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'getBranchProducts'])->name('branch-products');
        
        // Add product to branch inventory
        Route::post('/add-product-to-branch', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'addProductToBranchInventory'])->name('add-product-to-branch');
        
        // New simple inventory management
        Route::post('/add-all-products/{branch_id}', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'addAllProductsToBranch'])->name('add-all-products');
        Route::post('/restock-zero/{branch_id}', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'restockZeroQuantity'])->name('restock-zero');
        Route::post('/restock-main-zero', [\Botble\MultiBranchInventory\Http\Controllers\BranchInventoryController::class, 'restockZeroInMainBranch'])->name('restock-main-zero');
    });

    // Incoming Goods
    Route::group(['prefix' => 'incoming-goods', 'as' => 'incoming-goods.'], function () {
        Route::get('/', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'index'])->name('index');
        Route::get('/create', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'create'])->name('create');
        Route::get('/bulk-receive', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'bulkReceive'])->name('bulk-receive');
        Route::get('/get-pending', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'getPending'])->name('get-pending');
        Route::get('/reports', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'reports'])->name('reports');
        Route::get('/analytics-data', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'analyticsData'])->name('analytics-data');
        Route::get('/analytics/receiving', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'receivingAnalytics'])->name('receiving-analytics');
        Route::get('/analytics/variance', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'varianceAnalysis'])->name('variance-analytics');
        Route::get('/analytics/supplier', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'supplierPerformance'])->name('supplier-analytics');
        Route::get('/analytics/cost', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'costAnalysis'])->name('cost-analytics');
        Route::get('/analytics/inventory', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'inventoryImpact'])->name('inventory-analytics');
        Route::get('/analytics/quality', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'qualityReport'])->name('quality-analytics');
        Route::post('/bulk-process', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'bulkProcess'])->name('bulk-process');
        Route::post('/', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'store'])->name('store');
        Route::get('/{incomingGood}', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'show'])->name('show');
        Route::get('/{incomingGood}/edit', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'edit'])->name('edit');
        Route::put('/{incomingGood}', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'update'])->name('update');
        Route::delete('/{incomingGood}', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'destroy'])->name('destroy');
        Route::post('/{incomingGood}/process', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'process'])->name('process');
        Route::post('/{incomingGood}/duplicate', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'duplicate'])->name('duplicate');
        Route::get('/api/search-products', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'searchProducts'])->name('search-products');
        Route::get('/api/get-product-by-code', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'getProductByCode'])->name('get-product-by-code');
        Route::get('/api/suppliers', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'supplierSuggestions'])->name('api-suppliers');
        Route::post('/api/add-temp-product', [\Botble\MultiBranchInventory\Http\Controllers\IncomingGoodsController::class, 'addTemporaryProduct'])->name('api-add-temp-product');
    });

    // Temporary Products
    Route::group(['prefix' => 'temporary-products', 'as' => 'temporary-products.'], function () {
        Route::get('/', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'index'])->name('index');
        Route::get('/create', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'create'])->name('create');
        Route::post('/', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'store'])->name('store');
        Route::get('/{temporaryProduct}', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'show'])->name('show');
        Route::get('/{temporaryProduct}/edit', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'edit'])->name('edit');
        Route::put('/{temporaryProduct}', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'update'])->name('update');
        Route::post('/import-excel', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'importExcel'])->name('import-excel');
        Route::get('/export-excel', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'exportExcel'])->name('export-excel');
        Route::post('/{temporaryProduct}/convert', [\Botble\MultiBranchInventory\Http\Controllers\TemporaryProductController::class, 'convert'])->name('convert');
    });

    // Stock Transfers
    Route::group(['prefix' => 'stock-transfers', 'as' => 'stock-transfers.'], function () {
        Route::get('/', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'index'])->name('index');
        Route::get('/create', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'create'])->name('create');
        Route::post('/', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'store'])->name('store');
        Route::get('/{stockTransfer}', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'show'])->name('show');
        Route::get('/{stockTransfer}/edit', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'edit'])->name('edit');
        Route::put('/{stockTransfer}', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'update'])->name('update');
        Route::delete('/{stockTransfer}', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'destroy'])->name('destroy');
        Route::post('/{stockTransfer}/duplicate', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'duplicate'])->name('duplicate');
        Route::post('/{stockTransfer}/approve', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'approve'])->name('approve');
        Route::post('/{stockTransfer}/start-picking', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'startPicking'])->name('start-picking');
        Route::post('/{stockTransfer}/ship', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'ship'])->name('ship');
        Route::post('/{stockTransfer}/receive', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'receive'])->name('receive');
        Route::post('/{stockTransfer}/cancel', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'cancel'])->name('cancel');
        Route::get('/api/get-branch-products', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'getBranchProducts'])->name('get-branch-products');
        Route::get('/api/products', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'getProducts'])->name('get-products');
        Route::post('/quick-transfer', [\Botble\MultiBranchInventory\Http\Controllers\StockTransferController::class, 'quickTransfer'])->name('quick-transfer');
    });

    // Inventory Reports
    Route::group(['prefix' => 'inventory-reports', 'as' => 'inventory-reports.'], function () {
        Route::get('/', [\Botble\MultiBranchInventory\Http\Controllers\InventoryReportsController::class, 'index'])->name('index');
        Route::get('/low-stock', [\Botble\MultiBranchInventory\Http\Controllers\InventoryReportsController::class, 'lowStock'])->name('low-stock');
        Route::get('/stock-levels', [\Botble\MultiBranchInventory\Http\Controllers\InventoryReportsController::class, 'stockLevels'])->name('stock-levels');
        Route::get('/transfer-history', [\Botble\MultiBranchInventory\Http\Controllers\InventoryReportsController::class, 'transferHistory'])->name('transfer-history');
    });

});

// API Routes for POS Integration
Route::group([
    'prefix' => 'api/multi-branch-inventory/pos',
    'namespace' => 'Botble\MultiBranchInventory\Http\Controllers\Api',
    'middleware' => ['api'],
], function () {
    
    // POS Core API
    Route::get('/branches', 'PosApiController@getActiveBranches');
    Route::get('/products', 'PosApiController@getBranchProducts');
    Route::get('/search', 'PosApiController@searchProducts');
    Route::post('/scan', 'PosApiController@scanProduct');
    Route::post('/sale', 'PosApiController@processSale');
    Route::get('/alerts', 'PosApiController@getLowStockAlerts');
    Route::post('/stock-check', 'PosApiController@bulkStockCheck');
    Route::post('/reserve', 'PosApiController@reserveProducts');
    Route::post('/release', 'PosApiController@releaseReservation');
    
});

// Legacy API Routes for backwards compatibility
Route::group([
    'prefix' => 'api/multi-branch-inventory',
    'namespace' => 'Botble\MultiBranchInventory\Http\Controllers',
    'middleware' => ['api'],
], function () {
    
    // Branch Inventory API
    Route::get('/branch-stock', 'BranchInventoryController@getBranchStock');
    Route::post('/reserve-stock', 'BranchInventoryController@reserveStock');
    Route::post('/release-stock', 'BranchInventoryController@releaseStock');
    
    // Temporary Products API
    Route::get('/temporary-products/branch/{branchId}', 'TemporaryProductController@getBranchProducts');
    Route::get('/temporary-products/search', 'TemporaryProductController@searchForPos');
    Route::post('/temporary-products/{temporaryProduct}/sell', 'TemporaryProductController@sellQuantity');
    
    // Quick stock check
    Route::post('/check-availability', function (Illuminate\Http\Request $request) {
        $branchId = $request->branch_id;
        $items = $request->items; // [{'product_id': 1, 'quantity': 2}, ...]
        
        $availability = [];
        
        foreach ($items as $item) {
            $inventory = \Botble\MultiBranchInventory\Models\BranchInventory::where([
                'branch_id' => $branchId,
                'product_id' => $item['product_id'],
            ])->first();
            
            $availability[] = [
                'product_id' => $item['product_id'],
                'requested_quantity' => $item['quantity'],
                'available_quantity' => $inventory ? $inventory->quantity_available : 0,
                'can_fulfill' => $inventory && $inventory->quantity_available >= $item['quantity'],
                'price' => $inventory ? $inventory->effective_price : null,
            ];
        }
        
        return response()->json($availability);
    });
    
    // Integration Demo Routes
    Route::group(['prefix' => 'ecommerce-integration', 'as' => 'ecommerce-integration.'], function () {
        Route::get('/', 'EcommerceIntegrationDemoController@showProductsWithBranchInventory')->name('index');
        Route::get('/product/{id}/availability', 'EcommerceIntegrationDemoController@getProductAvailabilityAcrossBranches')->name('product-availability');
        Route::post('/product/{id}/sync', 'EcommerceIntegrationDemoController@syncProductWithBranches')->name('sync-product');
        Route::get('/reports/low-stock', 'EcommerceIntegrationDemoController@getLowStockReport')->name('low-stock-report');
        Route::get('/demo/inventory-sync', 'EcommerceIntegrationDemoController@demoInventorySync')->name('demo-sync');
        Route::post('/demo/simulate-sale', 'EcommerceIntegrationDemoController@simulateSaleSync')->name('simulate-sale');
    });
});

// Frontend Routes for Customer Interface
// Note: Frontend controllers need to be created before enabling these routes
/*
Route::group([
    'namespace' => 'Botble\MultiBranchInventory\Http\Controllers',
    'middleware' => ['web'],
], function () {
    
    // Branch Selector for Customers
    Route::get('/branches/nearby', [\Botble\MultiBranchInventory\Http\Controllers\Frontend\BranchController::class, 'getNearbyBranches']);
    Route::get('/products/{product}/availability', 'Frontend\ProductAvailabilityController@getAvailability');
    Route::post('/products/{product}/reserve', 'Frontend\ProductAvailabilityController@reserveForPickup');
    
});
*/