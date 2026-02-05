<?php

namespace App\Observers;

use App\Enums\BatchStatus;
use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Log;

class PurchaseObserver
{
    /**
     * Handle the Purchase "updated" event.
     * Auto-create product batches and stock movements when status changes to Received.
     */
    public function updated(Purchase $purchase): void
    {
        // Check if status was changed to Received
        if ($purchase->wasChanged('status') && $purchase->status === PurchaseStatus::Received) {
            $this->processReceivedPurchase($purchase);
        }
    }

    /**
     * Process received purchase - create batches and stock movements.
     */
    private function processReceivedPurchase(Purchase $purchase): void
    {
        Log::info('=== PURCHASE OBSERVER: Processing Received Purchase ===', [
            'purchase_id' => $purchase->id,
            'invoice' => $purchase->invoice_number,
        ]);

        $purchase->load('items.product');

        foreach ($purchase->items as $item) {
            // Calculate remaining quantity to receive
            $remainingQty = $item->quantity - ($item->received_quantity ?? 0);

            if ($remainingQty <= 0) {
                Log::info('Item already fully received', [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                ]);

                continue;
            }

            // Check if batch already exists for this item
            $existingBatch = ProductBatch::where('purchase_id', $purchase->id)
                ->where('product_id', $item->product_id)
                ->where('batch_number', $item->batch_number ?: 'BTH-'.now()->format('Ymd').'-'.$item->id)
                ->first();

            if ($existingBatch) {
                Log::info('Batch already exists for this item', [
                    'batch_id' => $existingBatch->id,
                    'product_id' => $item->product_id,
                ]);

                continue;
            }

            // Create new product batch
            $batchNumber = $item->batch_number ?: 'BTH-'.now()->format('Ymd').'-'.$item->id;
            $expiredDate = $item->expired_date ?: now()->addYears(2);

            $batch = ProductBatch::create([
                'product_id' => $item->product_id,
                'batch_number' => $batchNumber,
                'expired_date' => $expiredDate,
                'purchase_price' => $item->purchase_price,
                'selling_price' => $item->selling_price ?: $item->product->selling_price,
                'stock' => $remainingQty,
                'initial_stock' => $remainingQty,
                'supplier_id' => $purchase->supplier_id,
                'purchase_id' => $purchase->id,
                'status' => BatchStatus::Active,
            ]);

            Log::info('Created new product batch', [
                'batch_id' => $batch->id,
                'product_id' => $item->product_id,
                'batch_number' => $batchNumber,
                'stock' => $remainingQty,
            ]);

            // Create stock movement
            StockMovement::create([
                'product_batch_id' => $batch->id,
                'type' => StockMovementType::Purchase,
                'quantity' => $remainingQty,
                'stock_before' => 0,
                'stock_after' => $remainingQty,
                'reference_type' => Purchase::class,
                'reference_id' => $purchase->id,
                'notes' => "Penerimaan dari pembelian {$purchase->invoice_number}",
                'user_id' => auth()->id() ?? $purchase->user_id,
            ]);

            Log::info('Created stock movement', [
                'batch_id' => $batch->id,
                'quantity' => $remainingQty,
            ]);

            // Update received quantity
            $item->update([
                'received_quantity' => $item->quantity,
            ]);
        }

        Log::info('=== PURCHASE OBSERVER: Completed ===');
    }
}
