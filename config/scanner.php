<?php

return [
    'enable' => true,
    'enable_camera' => true,
    'enable_usb_scanner' => true,
    'barcode_field' => 'barcode', // field on products to use for barcode lookup (barcode|sku|meta)
    'custom_meta_key' => null, // set to a meta key if using product meta
    'show_stock' => true,
    'page_slug' => 'store-scan',
    'ui_theme' => 'light',
    'cache_ttl' => 60,
];
