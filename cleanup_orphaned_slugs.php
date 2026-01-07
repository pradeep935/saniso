<?php

/**
 * Script to clean up orphaned slugs from the database
 * Run with: php cleanup_orphaned_slugs.php
 * Run with --delete flag to actually delete: php cleanup_orphaned_slugs.php --delete
 */


# Step 1: Check what orphaned slugs exist (safe, no changes)
#php cleanup_orphaned_slugs.php

# Step 2: If you're happy with what it found, delete them
#php cleanup_orphaned_slugs.php --delete


require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Botble\Slug\Models\Slug;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Ecommerce\Models\Product;

$shouldDelete = in_array('--delete', $argv ?? []);

echo "=== ORPHANED SLUG CHECKER ===\n\n";

if (!$shouldDelete) {
    echo "Running in CHECK-ONLY mode. No changes will be made.\n";
    echo "Add --delete flag to actually delete orphaned slugs.\n\n";
}

// Check orphaned product category slugs
$orphanedCategorySlugs = Slug::query()
    ->where('reference_type', ProductCategory::class)
    ->get()
    ->filter(function ($slug) {
        return !ProductCategory::query()->where('id', $slug->reference_id)->exists();
    });

echo "Found " . $orphanedCategorySlugs->count() . " orphaned CATEGORY slugs:\n";
foreach ($orphanedCategorySlugs as $slug) {
    echo "  ❌ Slug: '{$slug->key}' (Slug ID: {$slug->id}, Missing Category ID: {$slug->reference_id})\n";
}

if ($shouldDelete && $orphanedCategorySlugs->count() > 0) {
    $orphanedCategorySlugs->each(fn ($slug) => $slug->delete());
    echo "\n✅ Deleted {$orphanedCategorySlugs->count()} orphaned category slugs.\n";
} elseif (!$shouldDelete && $orphanedCategorySlugs->count() > 0) {
    echo "\n⚠️  To delete these, run: php cleanup_orphaned_slugs.php --delete\n";
}

// Check orphaned product slugs
$orphanedProductSlugs = Slug::query()
    ->where('reference_type', Product::class)
    ->get()
    ->filter(function ($slug) {
        return !Product::query()->where('id', $slug->reference_id)->exists();
    });

echo "\nFound " . $orphanedProductSlugs->count() . " orphaned PRODUCT slugs:\n";
foreach ($orphanedProductSlugs as $slug) {
    echo "  ❌ Slug: '{$slug->key}' (Slug ID: {$slug->id}, Missing Product ID: {$slug->reference_id})\n";
}

if ($shouldDelete && $orphanedProductSlugs->count() > 0) {
    $orphanedProductSlugs->each(fn ($slug) => $slug->delete());
    echo "\n✅ Deleted {$orphanedProductSlugs->count()} orphaned product slugs.\n";
} elseif (!$shouldDelete && $orphanedProductSlugs->count() > 0) {
    echo "\n⚠️  To delete these, run: php cleanup_orphaned_slugs.php --delete\n";
}

// Show active slugs for comparison
$activeCategorySlugs = Slug::query()
    ->where('reference_type', ProductCategory::class)
    ->get()
    ->filter(function ($slug) {
        return ProductCategory::query()->where('id', $slug->reference_id)->exists();
    });

echo "\n" . str_repeat("=", 50) . "\n";
echo "✅ Active category slugs (will NOT be touched): " . $activeCategorySlugs->count() . "\n";
echo "✅ Active categories in database: " . ProductCategory::count() . "\n";

if ($shouldDelete) {
    echo "\n✅ Cleanup complete!\n";
} else {
    echo "\n✅ Check complete! No changes were made.\n";
}
