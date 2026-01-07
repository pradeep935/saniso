<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Botble\Ecommerce\Models\QuoteNotification;

class CleanDuplicateNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:clean-duplicates {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up duplicate quote notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('Starting duplicate notification cleanup...');
        
        // Find notifications with duplicate hashes
        $hashGroups = QuoteNotification::select('notification_hash')
            ->whereNotNull('notification_hash')
            ->groupBy('notification_hash')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('notification_hash');

        if ($hashGroups->isEmpty()) {
            $this->info('No duplicate notifications found.');
            return;
        }

        $this->info("Found {$hashGroups->count()} groups with duplicate hashes.");
        
        $totalDeleted = 0;

        foreach ($hashGroups as $hash) {
            $duplicates = QuoteNotification::where('notification_hash', $hash)
                ->orderBy('created_at', 'asc')
                ->get();
            
            // Keep the first one, delete the rest
            $keep = $duplicates->first();
            $toDelete = $duplicates->skip(1);
            
            $this->line("Hash {$hash}: Keeping ID {$keep->id}, " . ($dryRun ? 'would delete' : 'deleting') . " {$toDelete->count()} duplicates.");
            
            if (!$dryRun) {
                foreach ($toDelete as $duplicate) {
                    $duplicate->delete();
                    $totalDeleted++;
                }
            } else {
                $totalDeleted += $toDelete->count();
            }
        }

        if ($dryRun) {
            $this->info("Dry run complete. Would delete {$totalDeleted} duplicate notifications.");
        } else {
            $this->info("Cleanup complete. Deleted {$totalDeleted} duplicate notifications.");
        }
        
        $remaining = QuoteNotification::count();
        $this->info("Remaining notifications: {$remaining}");
    }
}
