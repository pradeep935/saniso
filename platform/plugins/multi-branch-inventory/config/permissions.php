<?php

return [
    [
        'name' => 'Multi-Branch Inventory',
        'flag' => 'plugins.multi-branch-inventory',
    ],
    [
        'name' => 'Branches',
        'flag' => 'branches.index',
        'parent_flag' => 'multi-branch-inventory.index',
    ],
    [
        'name' => 'Create Branch',
        'flag' => 'branches.create',
        'parent_flag' => 'branches.index',
    ],
    [
        'name' => 'Edit Branch',
        'flag' => 'branches.edit',
        'parent_flag' => 'branches.index',
    ],
    [
        'name' => 'Delete Branch',
        'flag' => 'branches.destroy',
        'parent_flag' => 'branches.index',
    ],
    [
        'name' => 'Branch Inventory',
        'flag' => 'branch-inventory.index',
        'parent_flag' => 'multi-branch-inventory.index',
    ],
    [
        'name' => 'Update Inventory',
        'flag' => 'branch-inventory.update',
        'parent_flag' => 'branch-inventory.index',
    ],
    [
        'name' => 'Adjust Stock',
        'flag' => 'branch-inventory.adjust-stock',
        'parent_flag' => 'branch-inventory.index',
    ],
    [
        'name' => 'Incoming Goods',
        'flag' => 'incoming-goods.index',
        'parent_flag' => 'multi-branch-inventory.index',
    ],
    [
        'name' => 'Create Incoming Goods',
        'flag' => 'incoming-goods.create',
        'parent_flag' => 'incoming-goods.index',
    ],
    [
        'name' => 'Process Incoming Goods',
        'flag' => 'incoming-goods.process',
        'parent_flag' => 'incoming-goods.index',
    ],
    [
        'name' => 'Temporary Products',
        'flag' => 'temporary-products.index',
        'parent_flag' => 'multi-branch-inventory.index',
    ],
    [
        'name' => 'Create Temporary Product',
        'flag' => 'temporary-products.create',
        'parent_flag' => 'temporary-products.index',
    ],
    [
        'name' => 'Edit Temporary Product',
        'flag' => 'temporary-products.edit',
        'parent_flag' => 'temporary-products.index',
    ],
    [
        'name' => 'Stock Transfers',
        'flag' => 'stock-transfers.index',
        'parent_flag' => 'multi-branch-inventory.index',
    ],
    [
        'name' => 'Create Stock Transfer',
        'flag' => 'stock-transfers.create',
        'parent_flag' => 'stock-transfers.index',
    ],
    [
        'name' => 'Approve Stock Transfer',
        'flag' => 'stock-transfers.approve',
        'parent_flag' => 'stock-transfers.index',
    ],
    [
        'name' => 'Manage Stock Transfer',
        'flag' => 'stock-transfers.manage',
        'parent_flag' => 'stock-transfers.index',
    ],
];