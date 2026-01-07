<?php

return [
    // Plugin Information
    'name' => 'Multi-Branch Inventory System',
    'version' => '1.0.0',
    'description' => 'Professional multi-branch inventory management with real-time synchronization',
    
    // Feature Toggles
    'features' => [
        'pos_integration' => true,
        'ecommerce_sync' => true,
        'pickup_reservations' => true,
        'stock_transfers' => true,
        'incoming_goods' => true,
        'inventory_reports' => true,
        'temporary_products' => true,
        'real_time_sync' => true,
    ],
    
    // Performance Settings
    'performance' => [
        'cache_inventory_data' => true,
        'cache_duration' => 300, // 5 minutes
        'batch_sync_size' => 100,
        'enable_async_processing' => true,
    ],
    
    // Security Settings
    'security' => [
        'require_branch_permission' => true,
        'enable_audit_trail' => true,
        'restrict_cross_branch_access' => true,
    ],
    
    // Notification Settings
    'notifications' => [
        'low_stock_threshold' => 5,
        'enable_email_alerts' => true,
        'enable_dashboard_alerts' => true,
        'notify_on_transfers' => true,
    ],
    
    // Integration Settings
    'integrations' => [
        'ecommerce' => [
            'auto_sync_inventory' => true,
            'sync_frequency' => 'real-time', // real-time, hourly, daily
            'reserve_stock_on_order' => true,
        ],
        'pos' => [
            'enable_temporary_products' => true,
            'auto_create_missing_products' => false,
            'require_branch_login' => true,
        ],
    ],
];