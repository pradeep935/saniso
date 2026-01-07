<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateMissingSkus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:generate-sku {--prefix=SKU- : Prefix for generated SKUs} {--random : Use random SKU instead of incremental} {--length=8 : Length for random portion} {--dry-run : Do not persist changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate SKUs for products that do not have one.';

    public function handle()
    {
        $prefix = $this->option('prefix');
        $useRandom = $this->option('random');
        $length = (int) $this->option('length');
        $dryRun = $this->option('dry-run');

        $this->info('Scanning products for missing SKUs...');

        // Adjust table name if different in your setup
        $table = 'ec_products';

        $products = DB::table($table)->whereNull('sku')->orWhere('sku', '')->get(['id', 'name']);

        if ($products->isEmpty()) {
            $this->info('No products without SKUs found.');
            return 0;
        }

        $this->info('Found ' . $products->count() . ' products without SKUs.');

        $generated = 0;

        foreach ($products as $product) {
            if ($useRandom) {
                // random code (alphanumeric)
                do {
                    $candidate = $prefix . Str::upper(Str::random($length));
                } while (DB::table($table)->where('sku', $candidate)->exists());
            } else {
                // incremental based on id
                $candidate = $prefix . $product->id;
                // ensure uniqueness (rare collision if prefix changed)
                $suffix = 0;
                while (DB::table($table)->where('sku', $candidate)->exists()) {
                    $suffix++;
                    $candidate = $prefix . $product->id . '-' . $suffix;
                }
            }

            if ($dryRun) {
                $this->line("[DRY] #{$product->id} {$product->name} => {$candidate}");
            } else {
                DB::table($table)->where('id', $product->id)->update(['sku' => $candidate]);
                $this->line("#{$product->id} {$product->name} => {$candidate}");
                $generated++;
            }
        }

        if ($dryRun) {
            $this->info('Dry-run complete. No changes were saved.');
        } else {
            $this->info("Done. Generated {$generated} SKUs.");
        }

        return 0;
    }
}
