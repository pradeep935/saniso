<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupeProductsByBarcode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:dedupe-by-barcode {--keep=latest : Keep "latest" (highest id) or "earliest" (lowest id)} {--dry-run : Do not delete, only show what would be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate rows in ec_products that share the same barcode, keeping one per barcode.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keep = $this->option('keep') === 'earliest' ? 'earliest' : 'latest';
        $dryRun = $this->option('dry-run');

        $this->info("Finding duplicate barcodes in table ec_products (keep={$keep}, dry-run=" . ($dryRun ? 'yes' : 'no') . ")...");

        // Find barcodes that appear more than once and are not null/empty
        $duplicates = DB::table('ec_products')
            ->select('barcode')
            ->whereNotNull('barcode')
            ->where('barcode', '<>', '')
            ->groupBy('barcode')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('barcode');

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate barcodes found.');
            return 0;
        }

        $this->info('Found ' . $duplicates->count() . ' barcodes with duplicates.');

        $totalDeleted = 0;

        foreach ($duplicates as $barcode) {
            $rows = DB::table('ec_products')
                ->where('barcode', $barcode)
                ->orderBy('id', $keep === 'latest' ? 'desc' : 'asc')
                ->get(['id', 'name', 'created_at']);

            // Keep the first row, delete the rest
            $keepId = $rows->first()->id;
            $idsToDelete = $rows->pluck('id')->filter(function ($id) use ($keepId) { return $id != $keepId; })->values()->all();

            if (empty($idsToDelete)) continue;

            if ($dryRun) {
                $this->line("[DRY] Barcode {$barcode}: would delete IDs: " . implode(',', $idsToDelete));
            } else {
                DB::table('ec_products')->whereIn('id', $idsToDelete)->delete();
                $this->line("Deleted duplicates for barcode {$barcode}: " . implode(',', $idsToDelete));
                $totalDeleted += count($idsToDelete);
            }
        }

        if (!$dryRun) {
            $this->info("Done. Deleted {$totalDeleted} duplicate product rows.");
        } else {
            $this->info('Dry-run complete. No rows were deleted.');
        }

        return 0;
    }
}
