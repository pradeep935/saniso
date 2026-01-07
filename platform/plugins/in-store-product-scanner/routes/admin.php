<?php

use Illuminate\Support\Facades\Route;
use Platform\InStoreProductScanner\Http\Controllers\Admin\SettingsController;

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'auth', 'core']], function () {
    Route::get('instore-scanner/settings', [SettingsController::class, 'index']);
    Route::post('instore-scanner/settings/save', [SettingsController::class, 'save']);
});
