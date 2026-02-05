<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Enums\StockOpnameStatus;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockOpnameService
{
    /**
     * Approve stock opname and apply adjustments to stock.
     */
    public function approve(StockOpname $opname, int $approvedBy): bool
    {
        if ($opname->status !== StockOpnameStatus::PendingApproval) {
            throw new \Exception('Stock opname harus dalam status Pending Approval untuk di-approve.');
        }

        DB::beginTransaction();

        try {
            $opname->load('items.productBatch');

            foreach ($opname->items as $item) {
                if (! $item->hasDiscrepancy()) {
                    continue;
                }

                $batch = $item->productBatch;
                if (! $batch) {
                    continue;
                }

                $difference = $item->difference; // physical - system
                $stockBefore = $batch->stock;

                // Apply adjustment
                $batch->stock = $item->physical_stock;
                $batch->save();

                $stockAfter = $batch->stock;

                // Determine movement type
                $movementType = $difference > 0
                    ? StockMovementType::AdjustmentIn
                    : StockMovementType::AdjustmentOut;

                // Create stock movement record
                StockMovement::create([
                    'product_batch_id' => $batch->id,
                    'type' => $movementType,
                    'quantity' => $difference, // Positive for in, negative for out
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => StockOpname::class,
                    'reference_id' => $opname->id,
                    'notes' => "Stock Opname #{$opname->code} - ".($difference > 0 ? 'Selisih lebih' : 'Selisih kurang').": {$difference}",
                    'user_id' => $approvedBy,
                ]);

                Log::info('Stock opname adjustment applied', [
                    'opname_id' => $opname->id,
                    'batch_id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'difference' => $difference,
                ]);
            }

            // Update opname status
            $opname->update([
                'status' => StockOpnameStatus::Approved,
                'approved_by' => $approvedBy,
                'approved_at' => now(),
            ]);

            DB::commit();

            Log::info('Stock opname approved', [
                'opname_id' => $opname->id,
                'code' => $opname->code,
                'approved_by' => $approvedBy,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Stock opname approval failed', [
                'opname_id' => $opname->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Cancel stock opname.
     */
    public function cancel(StockOpname $opname): bool
    {
        if ($opname->status === StockOpnameStatus::Approved) {
            throw new \Exception('Stock opname yang sudah di-approve tidak dapat dibatalkan.');
        }

        $opname->update(['status' => StockOpnameStatus::Cancelled]);

        return true;
    }

    /**
     * Submit stock opname for approval.
     */
    public function submitForApproval(StockOpname $opname): bool
    {
        if (! in_array($opname->status, [StockOpnameStatus::Draft, StockOpnameStatus::InProgress])) {
            throw new \Exception('Stock opname harus dalam status Draft atau In Progress.');
        }

        $opname->update(['status' => StockOpnameStatus::PendingApproval]);

        return true;
    }
}
