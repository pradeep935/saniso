<?php

return [
    'branches' => [
        'name' => 'Branches',
        'flag' => 'multi-branch-inventory.branches',
        'icon' => 'fa fa-building',
        'url' => 'admin/branches',
        'permissions' => ['multi-branch-inventory.branches.index'],
        'parent_id' => 'cms-plugins-ecommerce',
    ],
    'inventory' => [
        'name' => 'Branch Inventory',
        'flag' => 'multi-branch-inventory.inventory',
        'icon' => 'fa fa-boxes',
        'url' => 'admin/branch-inventory',
        'permissions' => ['multi-branch-inventory.inventory.index'],
        'parent_id' => 'cms-plugins-ecommerce',
    ],
    'transfers' => [
        'name' => 'Stock Transfers',
        'flag' => 'multi-branch-inventory.transfers',
        'icon' => 'fa fa-exchange-alt',
        'url' => 'admin/stock-transfers',
        'permissions' => ['multi-branch-inventory.transfers.index'],
        'parent_id' => 'cms-plugins-ecommerce',
    ],
    'incoming-goods' => [
        'name' => 'Incoming Goods',
        'flag' => 'multi-branch-inventory.incoming-goods',
        'icon' => 'fa fa-truck',
        'url' => 'admin/incoming-goods',
        'permissions' => ['multi-branch-inventory.incoming-goods.index'],
        'parent_id' => 'cms-plugins-ecommerce',
    ],
    'reports' => [
        'name' => 'Inventory Reports',
        'flag' => 'multi-branch-inventory.reports',
        'icon' => 'fa fa-chart-bar',
        'url' => 'admin/inventory-reports',
        'permissions' => ['inventory-reports.index'],
        'parent_id' => 'cms-plugins-ecommerce',
    ],
];