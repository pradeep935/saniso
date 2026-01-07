<?php

return [
    'name' => 'Short Links',
    'short_link' => 'Short Link',
    'short_links' => 'Short Links',
    'create' => 'Create Short Link',
    'edit' => 'Edit Short Link',
    'delete' => 'Delete Short Link',
    'short_link_details' => 'Short Link Details',
    'untitled' => 'Untitled Link',

    // Form fields
    'affiliate' => 'Affiliate',
    'title' => 'Title',
    'title_placeholder' => 'Enter a descriptive title for this short link',
    'short_code' => 'Short Code',
    'short_code_placeholder' => 'Enter unique short code (e.g., summer2024)',
    'short_code_help' => 'This will be used in the URL: yoursite.com/go/[short-code]',
    'destination_url' => 'Destination URL',
    'destination_url_help' => 'The full URL where users will be redirected',
    'product' => 'Product',
    'product_help' => 'Optional: Link this short link to a specific product for better tracking',
    'all_products' => 'All Products',
    'short_url' => 'Short URL',
    'clicks' => 'Clicks',
    'conversions' => 'Conversions',
    'conversion_rate' => 'Conversion Rate',
    'total_clicks' => 'Total Clicks',
    'total_conversions' => 'Total Conversions',
    'statistics' => 'Statistics',
    'actions' => 'Actions',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Validation messages
    'affiliate_required' => 'Please select an affiliate.',
    'affiliate_not_exists' => 'The selected affiliate does not exist.',
    'short_code_required' => 'Short code is required.',
    'short_code_unique' => 'This short code is already taken.',
    'short_code_alpha_dash' => 'Short code may only contain letters, numbers, dashes and underscores.',
    'destination_url_required' => 'Destination URL is required.',
    'destination_url_invalid' => 'Please enter a valid URL.',
    'product_not_exists' => 'The selected product does not exist.',

    // Actions
    'copy_url' => 'Copy URL',
    'copy_short_url' => 'Copy Short URL',
    'test_link' => 'Test Link',
    'visit_destination' => 'Visit Destination',
    'copied_to_clipboard' => 'URL copied to clipboard!',
    'copy_failed' => 'Failed to copy URL. Please try again.',

    // UI sections
    'url_information' => 'URL Information',
    'affiliate_product_info' => 'Affiliate & Product Information',
    'quick_actions' => 'Quick Actions',
    'performance' => 'Performance',
    'product_link' => 'Product Link',
    'general_link' => 'General Link',

    // Performance indicators
    'excellent' => 'Excellent',
    'good' => 'Good',
    'average' => 'Average',
    'no_data' => 'No Data',

    'back' => 'Back',

    // Status messages
    'affiliate_not_found' => 'Affiliate not found',
    'short_link_not_found' => 'Short link not found',

    // Table headers
    'affiliate_column' => 'Affiliate',
    'title_column' => 'Title & Code',
    'destination_column' => 'Destination',
    'short_url_column' => 'Short URL',
    'product_column' => 'Product',
    'stats_column' => 'Statistics',
    'created_column' => 'Created',

    // Bulk actions
    'bulk_delete_confirm' => 'Are you sure you want to delete these short links?',
    'bulk_delete_success' => 'Selected short links have been deleted successfully.',

    // Permissions
    'permissions' => [
        'index' => 'View short links',
        'create' => 'Create short links',
        'edit' => 'Edit short links',
        'destroy' => 'Delete short links',
    ],
];
