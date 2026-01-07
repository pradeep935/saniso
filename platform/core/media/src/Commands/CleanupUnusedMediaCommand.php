<?php

namespace Botble\Media\Commands;

use Botble\Media\Models\MediaFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanupUnusedMediaCommand extends Command
{
    protected $signature = 'media:cleanup
        {--days=180 : Clean up media files not used in products/posts older than N days}
        {--dry-run : Show what would be deleted without actually deleting}
        {--force : Skip confirmation}
        {--show-usage : Show where media files are used}';

    protected $description = 'Clean up unused media files to improve performance';

    protected array $usedMediaUrls = [];

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $showUsage = $this->option('show-usage');

        $this->info('Scanning for media file usage across all content...');
        
        // Collect all used media URLs
        $this->collectUsedMedia();

        if ($showUsage) {
            $this->displayUsageStats();
            return 0;
        }

        $cutoffDate = now()->subDays($days);

        // Find unused media files
        $allMedia = MediaFile::query()
            ->where('created_at', '<', $cutoffDate)
            ->get();

        $unused = $allMedia->filter(function ($media) {
            return !$this->isMediaUsed($media->url);
        });

        $orphanedCount = $unused->count();

        if ($orphanedCount === 0) {
            $this->info('No unused media files found older than ' . $days . ' days.');
            return 0;
        }

        if ($dryRun) {
            $this->info("[DRY RUN] Would delete {$orphanedCount} unused media files older than {$days} days.");
            $this->displayUnusedSamples($unused->take(10));
            return 0;
        }

        if (!$force && !$this->confirm("Delete {$orphanedCount} unused media files older than {$days} days?")) {
            $this->info('Aborted.');
            return 1;
        }

        $this->info("Deleting {$orphanedCount} unused media files...");
        
        $deleted = 0;
        $progressBar = $this->output->createProgressBar($orphanedCount);

        foreach ($unused as $file) {
            try {
                // Delete from storage
                if (Storage::exists($file->url)) {
                    Storage::delete($file->url);
                }
                
                // Delete thumbnails
                $thumbDir = dirname($file->url) . '/thumbs/';
                if (Storage::exists($thumbDir)) {
                    Storage::deleteDirectory($thumbDir);
                }
                
                // Delete from database
                $file->delete();
                $deleted++;
                $progressBar->advance();
            } catch (\Exception $e) {
                $this->warn("\nFailed to delete {$file->url}: {$e->getMessage()}");
            }
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("✓ Deleted {$deleted} unused media files.");

        // Optimize table
        $this->info("Optimizing media_files table...");
        DB::statement('OPTIMIZE TABLE media_files');
        $this->info("✓ Table optimized.");

        return 0;
    }

    protected function collectUsedMedia(): void
    {
        $this->usedMediaUrls = [];

        // 1. Product images (ec_products.images JSON column)
        $this->info('  → Checking product images...');
        DB::table('ec_products')
            ->whereNotNull('images')
            ->where('images', '!=', '[]')
            ->orderBy('id')
            ->select('images')
            ->chunk(500, function ($products) {
                foreach ($products as $product) {
                    $images = json_decode($product->images, true);
                    if (is_array($images)) {
                        foreach ($images as $img) {
                            if (is_string($img)) {
                                $this->usedMediaUrls[] = $img;
                            }
                        }
                    }
                }
            });

        // 2. Product featured image (ec_products.image column)
        $featuredImages = DB::table('ec_products')
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->pluck('image')
            ->toArray();
        $this->usedMediaUrls = array_merge($this->usedMediaUrls, $featuredImages);

        // 3. Blog posts content (embedded images in content)
        $this->info('  → Checking blog posts...');
        DB::table('posts')
            ->where(function ($query) {
                $query->where('content', 'LIKE', '%storage/%')
                      ->orWhere('content', 'LIKE', '%/products/%')
                      ->orWhere('content', 'LIKE', '%/brands/%');
            })
            ->orderBy('id')
            ->select('content')
            ->chunk(500, function ($posts) {
                foreach ($posts as $post) {
                    preg_match_all('/(?:src|href)=["\']([^"\']*(?:storage|products|brands)[^"\']*)["\']/', $post->content, $matches);
                    if (!empty($matches[1])) {
                        foreach ($matches[1] as $url) {
                            // Clean URL to match media_files format
                            $cleanUrl = preg_replace('#^.*/storage/#', '', $url);
                            $this->usedMediaUrls[] = $cleanUrl;
                        }
                    }
                }
            });

        // 4. Pages content
        $this->info('  → Checking pages...');
        if (DB::getSchemaBuilder()->hasTable('pages')) {
            DB::table('pages')
                ->where(function ($query) {
                    $query->where('content', 'LIKE', '%storage/%')
                          ->orWhere('content', 'LIKE', '%/products/%')
                          ->orWhere('content', 'LIKE', '%/brands/%');
                })
                ->orderBy('id')
                ->select('content')
                ->chunk(500, function ($pages) {
                    foreach ($pages as $page) {
                        preg_match_all('/(?:src|href)=["\']([^"\']*(?:storage|products|brands)[^"\']*)["\']/', $page->content, $matches);
                        if (!empty($matches[1])) {
                            foreach ($matches[1] as $url) {
                                $cleanUrl = preg_replace('#^.*/storage/#', '', $url);
                                $this->usedMediaUrls[] = $cleanUrl;
                            }
                        }
                    }
                });
        }

        // 5. Sliders/Galleries shortcodes
        $this->info('  → Checking sliders and galleries...');
        if (DB::getSchemaBuilder()->hasTable('simple_sliders')) {
            $sliderImages = DB::table('simple_slider_items')
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->pluck('image')
                ->toArray();
            $this->usedMediaUrls = array_merge($this->usedMediaUrls, $sliderImages);
        }

        // 6. Gallery images
        if (DB::getSchemaBuilder()->hasTable('galleries')) {
            DB::table('gallery_meta')
                ->where('key', 'images')
                ->orderBy('id')
                ->select('value')
                ->chunk(500, function ($metas) {
                    foreach ($metas as $meta) {
                        $images = json_decode($meta->value, true);
                        if (is_array($images)) {
                            foreach ($images as $img) {
                                if (is_string($img)) {
                                    $this->usedMediaUrls[] = $img;
                                } elseif (isset($img['img'])) {
                                    $this->usedMediaUrls[] = $img['img'];
                                }
                            }
                        }
                    }
                });
        }

        // 7. Theme options (logo, favicon, etc.)
        $this->info('  → Checking theme options...');
        if (DB::getSchemaBuilder()->hasTable('settings')) {
            DB::table('settings')
                ->where(function ($query) {
                    $query->where('key', 'LIKE', '%logo%')
                          ->orWhere('key', 'LIKE', '%icon%')
                          ->orWhere('key', 'LIKE', '%image%')
                          ->orWhere('key', 'LIKE', '%banner%');
                })
                ->orderBy('id')
                ->select('value')
                ->chunk(500, function ($settings) {
                    foreach ($settings as $setting) {
                        if (is_string($setting->value) && str_contains($setting->value, '/')) {
                            $this->usedMediaUrls[] = $setting->value;
                        }
                    }
                });
        }

        // 8. Meta boxes (featured images, SEO images)
        $this->info('  → Checking meta boxes...');
        if (DB::getSchemaBuilder()->hasTable('meta_boxes')) {
            DB::table('meta_boxes')
                ->whereIn('meta_key', ['image', 'thumbnail', 'featured_image', 'seo_image'])
                ->orderBy('id')
                ->select('meta_value')
                ->chunk(500, function ($metaBoxes) {
                    foreach ($metaBoxes as $meta) {
                        if (is_string($meta->meta_value)) {
                            $this->usedMediaUrls[] = $meta->meta_value;
                        }
                    }
                });
        }

        // 9. Product categories
        if (DB::getSchemaBuilder()->hasTable('ec_product_categories')) {
            $categoryImages = DB::table('ec_product_categories')
                ->whereNotNull('image')
                ->where('image', '!=', '')
                ->pluck('image')
                ->toArray();
            $this->usedMediaUrls = array_merge($this->usedMediaUrls, $categoryImages);
        }

        // 10. Brands
        if (DB::getSchemaBuilder()->hasTable('ec_brands')) {
            $brandImages = DB::table('ec_brands')
                ->whereNotNull('logo')
                ->where('logo', '!=', '')
                ->pluck('logo')
                ->toArray();
            $this->usedMediaUrls = array_merge($this->usedMediaUrls, $brandImages);
        }

        // Remove duplicates and clean URLs
        $this->usedMediaUrls = array_unique($this->usedMediaUrls);
        $this->usedMediaUrls = array_map(function ($url) {
            // Remove domain, /storage/, etc to match media_files.url format
            return preg_replace('#^(?:https?://[^/]+)?(?:/storage/)?#', '', $url);
        }, $this->usedMediaUrls);

        $this->info('  ✓ Found ' . count($this->usedMediaUrls) . ' media files in use');
    }

    protected function isMediaUsed(string $url): bool
    {
        return in_array($url, $this->usedMediaUrls);
    }

    protected function displayUsageStats(): void
    {
        $totalMedia = MediaFile::count();
        $usedCount = count($this->usedMediaUrls);
        $unusedCount = $totalMedia - $usedCount;

        $this->newLine();
        $this->info('═══════════════════════════════════════════');
        $this->info('Media Usage Statistics');
        $this->info('═══════════════════════════════════════════');
        $this->info("Total media files:    {$totalMedia}");
        $this->info("Files in use:         {$usedCount}");
        $this->info("Unused files:         {$unusedCount}");
        $this->info('═══════════════════════════════════════════');
    }

    protected function displayUnusedSamples($samples): void
    {
        $this->newLine();
        $this->info('Sample of files that would be deleted:');
        foreach ($samples as $file) {
            $size = $file->size ? round($file->size / 1024, 2) . ' KB' : 'unknown';
            $this->line("  [{$file->id}] {$file->url} ({$size}, created: {$file->created_at})");
        }
    }
}
