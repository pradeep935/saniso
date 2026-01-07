<?php

namespace Botble\Base\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneAuditHistoriesCommand extends Command
{
    protected $signature = 'audit-histories:prune
        {--days=90 : Number of days to keep (default: 90 days)}
        {--force : Force delete without confirmation}';

    protected $description = 'Prune old audit history records to maintain database performance.';

    public function handle(): int
    {
        $days = (int)$this->option('days');
        $force = $this->option('force');
        
        $cutoffDate = now()->subDays($days)->toDateTimeString();

        $count = DB::table('audit_histories')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($count === 0) {
            $this->info('No audit records to delete.');
            return 0;
        }

        if (!$force && !$this->confirm("Delete {$count} audit records older than {$days} days?")) {
            $this->info('Aborted.');
            return 1;
        }

        $this->info("Deleting {$count} old audit records...");
        
        DB::table('audit_histories')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        // Optimize table to reclaim space
        DB::statement('OPTIMIZE TABLE audit_histories');

        $this->info("✓ Deleted {$count} audit records and optimized table.");

        return 0;
    }
}
