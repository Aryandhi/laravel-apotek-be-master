<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
use App\Models\Product;
use App\Services\BatchPricingSyncService;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
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

    protected function afterCreate(): void
    {
        app(BatchPricingSyncService::class)->syncPurchaseItemsToExistingBatches($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
