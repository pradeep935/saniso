<?php

return [
    [
        'name' => 'Product Bulk Edit',
        'flag' => 'product-bulk-edit.index',
        'parent_flag' => 'products.index',
    ],
    [
        'name' => 'Update Products',
        'flag' => 'product-bulk-edit.update',
        'parent_flag' => 'product-bulk-edit.index',
    ],
];
