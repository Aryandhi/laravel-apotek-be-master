<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Product;
use App\Services\BatchPricingSyncService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $service = app(BatchPricingSyncService::class);

        foreach (($data['items'] ?? []) as $index => $item) {
            $productId = $item['product_id'] ?? null;

            if (! $productId) {
                continue;
            }

            $batch = $service->findBatchByProductAndNumber((int) $productId, $item['batch_number'] ?? null)
                ?? $service->findLatestBatchByProduct((int) $productId);

            $product = Product::query()->find((int) $productId);
            if ($product?->base_unit_id) {
                $data['items'][$index]['unit_id'] = (int) $product->base_unit_id;
            }

            if (! $batch) {
                $data['items'][$index]['is_manual_selling_price'] = false;

                continue;
            }

            $data['items'][$index]['purchase_price'] = $batch->purchase_price;
            $data['items'][$index]['margin_percentage'] = $batch->margin_percentage;
            $data['items'][$index]['selling_price'] = $batch->selling_price;
            $data['items'][$index]['is_manual_selling_price'] = false;
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $items = $data['items'] ?? [];
        $productIds = collect($items)
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($productIds->isEmpty()) {
            return $data;
        }

        $baseUnitsByProduct = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('base_unit_id', 'id');

        foreach ($items as $index => $item) {
            $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;

            if (! $productId) {
                continue;
            }

            $baseUnitId = $baseUnitsByProduct->get($productId);
            if ($baseUnitId) {
                $data['items'][$index]['unit_id'] = (int) $baseUnitId;
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        app(BatchPricingSyncService::class)->syncPurchaseItemsToExistingBatches($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
