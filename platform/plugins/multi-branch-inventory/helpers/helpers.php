<?php

if (! function_exists('get_current_branch')) {
    function get_current_branch()
    {
        // Get current branch from session or default
        return session('current_branch_id', 1);
    }
}

if (! function_exists('set_current_branch')) {
    function set_current_branch($branchId)
    {
        session(['current_branch_id' => $branchId]);
    }
}

if (! function_exists('format_inventory_status')) {
    function format_inventory_status($quantity)
    {
        if ($quantity <= 0) {
            return '<span class="label label-danger">Out of Stock</span>';
        } elseif ($quantity <= 10) {
            return '<span class="label label-warning">Low Stock</span>';
        } else {
            return '<span class="label label-success">In Stock</span>';
        }
    }
}

if (! function_exists('calculate_inventory_value')) {
    function calculate_inventory_value($quantity, $cost_price)
    {
        return $quantity * $cost_price;
    }
}