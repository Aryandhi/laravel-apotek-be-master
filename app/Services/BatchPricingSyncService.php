<?php

namespace App\Services;

use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;

class BatchPricingSyncService
{
    public function calculateSellingPrice(mixed $purchasePrice, mixed $marginPercentage): float
    {
        $resolvedPurchasePrice = $this->toFloat($purchasePrice);
        $resolvedMarginPercentage = $this->toFloat($marginPercentage);

        if ($resolvedPurchasePrice <= 0) {
            return 0.0;
        }

        return round($resolvedPurchasePrice * (1 + ($resolvedMarginPercentage / 100)), 2);
    }

    public function calculateMarginPercentage(mixed $purchasePrice, mixed $sellingPrice): float
    {
        $resolvedPurchasePrice = $this->toFloat($purchasePrice);
        $resolvedSellingPrice = $this->toFloat($sellingPrice);

        if ($resolvedPurchasePrice <= 0 || $resolvedSellingPrice <= 0) {
            return 0.0;
        }

        return round((($resolvedSellingPrice - $resolvedPurchasePrice) / $resolvedPurchasePrice) * 100, 2);
    }

    /**
     * @return array{purchase_price: float, margin_percentage: float, selling_price: float}
     */
    public function normalizePricing(mixed $purchasePrice, mixed $marginPercentage, mixed $sellingPrice, bool $preferSellingPrice = false): array
    {
        $resolvedPurchasePrice = $this->toFloat($purchasePrice);
        $resolvedMarginPercentage = $this->toFloat($marginPercentage);
        $resolvedSellingPrice = $this->toFloat($sellingPrice);

        if ($preferSellingPrice && $resolvedPurchasePrice > 0 && $resolvedSellingPrice > 0) {
            $resolvedMarginPercentage = $this->calculateMarginPercentage($resolvedPurchasePrice, $resolvedSellingPrice);
        } elseif ($resolvedPurchasePrice > 0 && $resolvedMarginPercentage > 0) {
            $resolvedSellingPrice = $this->calculateSellingPrice($resolvedPurchasePrice, $resolvedMarginPercentage);
        } elseif ($resolvedPurchasePrice > 0 && $resolvedSellingPrice > 0) {
            $resolvedMarginPercentage = $this->calculateMarginPercentage($resolvedPurchasePrice, $resolvedSellingPrice);
        } else {
            $resolvedSellingPrice = $this->calculateSellingPrice($resolvedPurchasePrice, $resolvedMarginPercentage);
        }

        return [
            'purchase_price' => round($resolvedPurchasePrice, 2),
            'margin_percentage' => round($resolvedMarginPercentage, 2),
            'selling_price' => round($resolvedSellingPrice, 2),
        ];
    }

    public function syncPurchaseItemsToExistingBatches(Purchase $purchase): void
    {
        $purchase->loadMissing('items');

        foreach ($purchase->items as $item) {
            $this->syncPurchaseItemToExistingBatch($item, $purchase->supplier_id, $purchase->id);
        }
    }

    public function syncPurchaseItemToExistingBatch(PurchaseItem $item, ?int $supplierId = null, ?int $purchaseId = null): void
    {
        $batchNumber = trim((string) ($item->batch_number ?? ''));

        if (! $item->product_id || $batchNumber === '') {
            return;
        }

        $batch = ProductBatch::query()
            ->where('product_id', $item->product_id)
            ->where('batch_number', $batchNumber)
            ->first();

        if (! $batch) {
            return;
        }

        $pricing = $this->normalizePricing(
            $item->purchase_price,
            $item->margin_percentage,
            $item->selling_price,
            preferSellingPrice: $this->toFloat($item->selling_price) > 0
        );

        $batch->update([
            'purchase_price' => $pricing['purchase_price'],
            'margin_percentage' => $pricing['margin_percentage'],
            'selling_price' => $pricing['selling_price'],
            'supplier_id' => $batch->supplier_id ?? $supplierId,
            'purchase_id' => $batch->purchase_id ?? $purchaseId,
        ]);

        $item->update([
            'purchase_price' => $pricing['purchase_price'],
            'margin_percentage' => $pricing['margin_percentage'],
            'selling_price' => $pricing['selling_price'],
        ]);
    }

    public function syncBatchPricingToPurchaseItems(ProductBatch $batch): void
    {
        $pricing = $this->normalizePricing(
            $batch->purchase_price,
            $batch->margin_percentage,
            $batch->selling_price,
            preferSellingPrice: $this->toFloat($batch->selling_price) > 0
        );

        PurchaseItem::query()
            ->where('product_id', $batch->product_id)
            ->where('batch_number', $batch->batch_number)
            ->update([
                'purchase_price' => $pricing['purchase_price'],
                'margin_percentage' => $pricing['margin_percentage'],
                'selling_price' => $pricing['selling_price'],
            ]);
    }

    public function findLatestBatchByProduct(int $productId): ?ProductBatch
    {
        return ProductBatch::query()
            ->where('product_id', $productId)
            ->latest('id')
            ->first();
    }

    public function findBatchByProductAndNumber(int $productId, ?string $batchNumber): ?ProductBatch
    {
        $resolvedBatchNumber = trim((string) ($batchNumber ?? ''));

        if ($resolvedBatchNumber === '') {
            return null;
        }

        return ProductBatch::query()
            ->where('product_id', $productId)
            ->where('batch_number', $resolvedBatchNumber)
            ->first();
    }

    private function toFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }
}
