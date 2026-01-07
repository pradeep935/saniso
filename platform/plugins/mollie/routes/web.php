<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Mollie\Http\Controllers\MollieController;
use Botble\Mollie\Http\Controllers\MollieTerminalController;
use Botble\Mollie\Http\Controllers\PosPaymentController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['core']], function (): void {
    // Original Mollie routes
    Route::post('mollie/payment/callback/{token}', [MollieController::class, 'paymentCallback'])
        ->name('mollie.payment.callback');
    
    // Webhook for terminal payments (auto-complete orders) - public route
    Route::post('mollie/terminal/webhook', [MollieTerminalController::class, 'terminalWebhook'])
        ->name('mollie.terminal.webhook');
});

// Admin routes for POS Terminal
Route::group([
    'prefix' => BaseHelper::getAdminPrefix(),
    'middleware' => ['web', 'core', 'auth']
], function () {
    
    // Main Mollie Dashboard (integrated with admin)
    Route::group(['prefix' => 'mollie'], function () {
        Route::get('dashboard', [MollieController::class, 'dashboard'])
            ->name('mollie.dashboard');
        Route::get('analytics', [MollieController::class, 'analytics'])
            ->name('mollie.analytics');
        Route::get('terminal-status', [MollieController::class, 'terminalStatus'])
            ->name('mollie.terminal-status');
        Route::post('manual-payment', [MollieController::class, 'manualPayment'])
            ->name('mollie.manual-payment');
        
        // Terminal Device Management
        Route::get('terminals', [MollieController::class, 'getTerminals'])
            ->name('mollie.dashboard.terminals');
        Route::post('add-terminal', [MollieController::class, 'addTerminal'])
            ->name('mollie.dashboard.add-terminal');
        Route::post('remove-terminal', [MollieController::class, 'removeTerminal'])
            ->name('mollie.dashboard.remove-terminal');
        Route::get('pending-orders', [MollieController::class, 'getPendingOrders'])
            ->name('mollie.dashboard.pending-orders');
    });

    Route::group(['prefix' => 'mollie/terminal'], function () {
        // OLD POS Dashboard View (DISABLED - Use main dashboard instead)
        // Route::get('dashboard', [MollieTerminalController::class, 'dashboard'])
        //     ->name('mollie.terminal.dashboard');
        
        // POS Dashboard API
        Route::get('pos-dashboard', [MollieTerminalController::class, 'posDashboard'])
            ->name('mollie.terminal.dashboard.api');
        
        // Send payment to terminal
        Route::post('send-payment', [MollieTerminalController::class, 'sendToTerminal'])
            ->name('mollie.terminal.send');
        
        // Check payment status
        Route::get('payment-status', [MollieTerminalController::class, 'checkPaymentStatus'])
            ->name('mollie.terminal.status');
        
        // Cancel terminal payment
        Route::post('cancel-payment', [MollieTerminalController::class, 'cancelPayment'])
            ->name('mollie.terminal.cancel');
        
        // Refund terminal payment
        Route::post('refund-payment', [MollieTerminalController::class, 'refundPayment'])
            ->name('mollie.terminal.refund');
        
        // Get available terminals
        Route::get('terminals', [MollieTerminalController::class, 'getTerminals'])
            ->name('mollie.terminal.list');
    });
    
    // POS Payment API routes
    Route::group(['prefix' => 'mollie/pos'], function () {
        // Validate Mollie configuration
        Route::get('validate-config', [PosPaymentController::class, 'validateMollieConfig'])
            ->name('mollie.pos.validate');
        
        // Process terminal payment from POS
        Route::post('process-payment', [PosPaymentController::class, 'processTerminalPayment'])
            ->name('mollie.pos.process');
        
        // Check payment status
        Route::get('payment-status', [PosPaymentController::class, 'checkPaymentStatus'])
            ->name('mollie.pos.status');
        
        // Cancel payment
        Route::post('cancel-payment', [PosPaymentController::class, 'cancelTerminalPayment'])
            ->name('mollie.pos.cancel');
    });
});
