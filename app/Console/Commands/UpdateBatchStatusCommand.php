<?php

namespace App\Console\Commands;

use App\Models\ProductBatch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateBatchStatusCommand extends Command
{
    protected $signature = 'batch:update-status {--days=90 : Days before expiry to mark as near-expired}';

    protected $description = 'Update batch status based on expiry dates (Active → NearExpired → Expired)';

    public function handle(): int
    {
        $nearExpiredDays = (int) $this->option('days');

        $this->info("Updating batch statuses (near-expired threshold: {$nearExpiredDays} days)...");
        $summary = ProductBatch::syncExpiryStatuses($nearExpiredDays);

        if ($summary['expired'] > 0) {
            $this->warn("Marked {$summary['expired']} batch(es) as EXPIRED");
            Log::channel('single')->warning("Batch status update: {$summary['expired']} batches marked as expired");
        }

        if ($summary['near_expired'] > 0) {
            $this->info("Marked {$summary['near_expired']} batch(es) as NEAR-EXPIRED");
            Log::channel('single')->info("Batch status update: {$summary['near_expired']} batches marked as near-expired");
        }

        if ($summary['active'] > 0) {
            $this->info("Restored {$summary['active']} batch(es) to ACTIVE");
            Log::channel('single')->info("Batch status update: {$summary['active']} batches restored to active");
        }

        if ($summary['total_updated'] === 0) {
            $this->info('No batches needed status update.');
        } else {
            $this->newLine();
            $this->table(
                ['Status', 'Count'],
                [
                    ['Expired', $summary['expired']],
                    ['Near Expired', $summary['near_expired']],
                    ['Active', $summary['active']],
                    ['Total Updated', $summary['total_updated']],
                ]
            );
        }

        return Command::SUCCESS;
    }
}
