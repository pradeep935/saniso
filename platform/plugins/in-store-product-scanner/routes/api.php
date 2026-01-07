<?php

use Illuminate\Support\Facades\Route;
use Platform\InStoreProductScanner\Http\Controllers\Api\ScanController;

Route::group(['prefix' => 'api/store-scan', 'middleware' => ['api', 'throttle:30,1']], function () {
    Route::post('/lookup', [ScanController::class, 'lookup'])->name('instore.api.lookup');
});
