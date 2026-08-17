<?php

namespace App\Filament\Resources\Purchases\Pages;

use App\Filament\Resources\Purchases\PurchaseResource;
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
