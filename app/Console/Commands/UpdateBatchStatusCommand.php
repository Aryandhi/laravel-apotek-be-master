<?php

namespace App\Console\Commands;

use App\Enums\BatchStatus;
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
        $today = now()->startOfDay();

        $this->info("Updating batch statuses (near-expired threshold: {$nearExpiredDays} days)...");

        // 1. Mark expired batches
        $expiredCount = ProductBatch::whereIn('status', [BatchStatus::Active, BatchStatus::NearExpired])
            ->where('expired_date', '<', $today)
            ->update(['status' => BatchStatus::Expired]);

        if ($expiredCount > 0) {
            $this->warn("Marked {$expiredCount} batch(es) as EXPIRED");
            Log::channel('single')->warning("Batch status update: {$expiredCount} batches marked as expired");
        }

        // 2. Mark near-expired batches (within X days of expiry)
        $nearExpiredCount = ProductBatch::where('status', BatchStatus::Active)
            ->where('expired_date', '>=', $today)
            ->where('expired_date', '<=', $today->copy()->addDays($nearExpiredDays))
            ->update(['status' => BatchStatus::NearExpired]);

        if ($nearExpiredCount > 0) {
            $this->info("Marked {$nearExpiredCount} batch(es) as NEAR-EXPIRED");
            Log::channel('single')->info("Batch status update: {$nearExpiredCount} batches marked as near-expired");
        }

        // 3. Summary
        $summary = [
            'expired' => $expiredCount,
            'near_expired' => $nearExpiredCount,
            'total_updated' => $expiredCount + $nearExpiredCount,
        ];

        if ($summary['total_updated'] === 0) {
            $this->info('No batches needed status update.');
        } else {
            $this->newLine();
            $this->table(
                ['Status', 'Count'],
                [
                    ['Expired', $expiredCount],
                    ['Near Expired', $nearExpiredCount],
                    ['Total Updated', $summary['total_updated']],
                ]
            );
        }

        return Command::SUCCESS;
    }
}
