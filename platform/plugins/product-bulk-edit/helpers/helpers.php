<?php

if (! function_exists('product_bulk_edit_enabled')) {
    function product_bulk_edit_enabled(): bool
    {
        return is_plugin_active('product-bulk-edit') && is_plugin_active('ecommerce');
    }
}

if (! function_exists('product_bulk_edit_route')) {
    function product_bulk_edit_route(): string
    {
        return route('product-bulk-edit.index');
    }
}
