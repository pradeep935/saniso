<?php

namespace Botble\Ecommerce\Console;

use Botble\Ecommerce\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateProductSkusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecommerce:generate-skus {--force : Overwrite existing SKUs} {--prefix= : Optional SKU prefix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing SKUs for products (safe by default)';

    public function handle()
    {
        $force = $this->option('force');
        $prefix = $this->option('prefix') ? trim($this->option('prefix')) : '';

        $this->info('Starting SKU generation...');

        $query = Product::query();

        if (! $force) {
            $query->where(function ($q) {
                $q->whereNull('sku')->orWhere('sku', '');
            });
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info('No products to process (use --force to overwrite existing SKUs).');
            return 0;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $generated = 0;

        $query->chunkById(200, function ($products) use (&$generated, &$bar, $force, $prefix) {
            /** @var Product $product */
            foreach ($products as $product) {
                $originalSku = $product->sku;

                if (! $force && $originalSku) {
                    $bar->advance();
                    continue;
                }

                // Try to build a clean SKU: prefix + P + zero-padded id
                $skuBase = ($prefix ? strtoupper($prefix) . '-' : '') . 'P' . str_pad($product->id, 6, '0', STR_PAD_LEFT);

                // Ensure uniqueness: append suffix if collision
                $sku = $skuBase;
                $i = 1;
                while (Product::query()->where('sku', $sku)->where('id', '!=', $product->id)->exists()) {
                    $sku = $skuBase . '-' . $i++;
                }

                $product->sku = $sku;
                $product->save();

                $generated++;
                $bar->advance();
            }
        });

        $bar->finish();
        $this->line('');

        $this->info(sprintf('Completed. SKUs generated/updated: %d', $generated));

        return 0;
    }
}
