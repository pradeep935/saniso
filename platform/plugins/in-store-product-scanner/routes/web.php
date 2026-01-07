<?php

use Illuminate\Support\Facades\Route;
use Platform\InStoreProductScanner\Http\Controllers\ScannerController;

Route::group(['middleware' => ['web']], function () {
    Route::get('/store-scan', [ScannerController::class, 'index'])->name('instore.scanner');
});
