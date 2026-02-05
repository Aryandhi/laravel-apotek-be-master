<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Setting;

class StockAllocationService
{
    /**
     * Allocate stock from multiple batches for a product.
     * Uses FEFO (First Expired First Out) or FIFO (First In First Out) based on settings.
     *
     * @param  int  $productId  The product ID
     * @param  int  $quantityNeeded  The quantity needed
     * @param  int|null  $preferredBatchId  Optional preferred batch to use first
     * @return array{success: bool, allocations: array, message: string, total_allocated: int}
     */
    public function allocateStock(int $productId, int $quantityNeeded, ?int $preferredBatchId = null): array
    {
        $method = Setting::get('fifo_method', 'fefo');

        // Get available batches ordered by the selected method
        $batchesQuery = ProductBatch::where('product_id', $productId)
            ->available() // status = active AND stock > 0
            ->where('expired_date', '>', now()); // not expired

        // Order by method
        if ($method === 'fefo') {
            $batchesQuery->orderBy('expired_date', 'asc'); // First Expired First Out
        } else {
            $batchesQuery->orderBy('created_at', 'asc'); // First In First Out
        }

        $batches = $batchesQuery->get();

        // If preferred batch specified, move it to front
        if ($preferredBatchId) {
            $preferredBatch = $batches->firstWhere('id', $preferredBatchId);
            if ($preferredBatch) {
                $batches = $batches->reject(fn ($b) => $b->id === $preferredBatchId);
                $batches->prepend($preferredBatch);
            }
        }

        // Calculate total available stock
        $totalAvailable = $batches->sum('stock');

        if ($totalAvailable < $quantityNeeded) {
            return [
                'success' => false,
                'allocations' => [],
                'message' => "Stok tidak mencukupi. Dibutuhkan: {$quantityNeeded}, Tersedia: {$totalAvailable}",
                'total_allocated' => 0,
                'total_available' => $totalAvailable,
            ];
        }

        // Allocate from batches
        $allocations = [];
        $remaining = $quantityNeeded;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $allocateFromBatch = min($batch->stock, $remaining);

            $allocations[] = [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expired_date' => $batch->expired_date->format('Y-m-d'),
                'quantity' => $allocateFromBatch,
                'selling_price' => $batch->selling_price,
                'available_stock' => $batch->stock,
            ];

            $remaining -= $allocateFromBatch;
        }

        return [
            'success' => true,
            'allocations' => $allocations,
            'message' => 'Stok berhasil dialokasikan',
            'total_allocated' => $quantityNeeded,
            'total_available' => $totalAvailable,
        ];
    }

    /**
     * Validate if the requested quantity can be fulfilled from available stock.
     *
     * @param  int  $productId  The product ID
     * @param  int  $quantityNeeded  The quantity needed
     * @return array{available: bool, total_stock: int, message: string}
     */
    public function validateStock(int $productId, int $quantityNeeded): array
    {
        $totalStock = ProductBatch::where('product_id', $productId)
            ->available()
            ->where('expired_date', '>', now())
            ->sum('stock');

        if ($totalStock >= $quantityNeeded) {
            return [
                'available' => true,
                'total_stock' => $totalStock,
                'message' => 'Stok mencukupi',
            ];
        }

        return [
            'available' => false,
            'total_stock' => $totalStock,
            'message' => "Stok tidak mencukupi. Dibutuhkan: {$quantityNeeded}, Tersedia: {$totalStock}",
        ];
    }

    /**
     * Get total available stock for a product (excluding expired batches).
     */
    public function getAvailableStock(int $productId): int
    {
        return ProductBatch::where('product_id', $productId)
            ->available()
            ->where('expired_date', '>', now())
            ->sum('stock');
    }
}
