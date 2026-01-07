<?php

return [
    [
        'name' => 'Affiliate',
        'flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'affiliate-pro.create',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'affiliate-pro.edit',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'affiliate-pro.destroy',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Commissions',
        'flag' => 'affiliate.commissions.index',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Withdrawals',
        'flag' => 'affiliate.withdrawals.index',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Reports',
        'flag' => 'affiliate.reports',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Coupons',
        'flag' => 'affiliate.coupons.index',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'affiliate.coupons.create',
        'parent_flag' => 'affiliate.coupons.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'affiliate.coupons.edit',
        'parent_flag' => 'affiliate.coupons.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'affiliate.coupons.destroy',
        'parent_flag' => 'affiliate.coupons.index',
    ],
    [
        'name' => 'Short Links',
        'flag' => 'affiliate.short-links.index',
        'parent_flag' => 'affiliate-pro.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'affiliate.short-links.create',
        'parent_flag' => 'affiliate.short-links.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'affiliate.short-links.edit',
        'parent_flag' => 'affiliate.short-links.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'affiliate.short-links.destroy',
        'parent_flag' => 'affiliate.short-links.index',
    ],
    [
        'name' => 'Settings',
        'flag' => 'affiliate.settings',
        'parent_flag' => 'affiliate-pro.index',
    ],
];