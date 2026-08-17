<?php

namespace App\Filament\Resources\ProductBatches\Pages;

use App\Filament\Resources\ProductBatches\ProductBatchResource;
use App\Services\BatchPricingSyncService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductBatch extends EditRecord
{
    protected static string $resource = ProductBatchResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $service = app(BatchPricingSyncService::class);

        $marginPercentage = (float) ($data['margin_percentage'] ?? 0);
        $purchasePrice = (float) ($data['purchase_price'] ?? 0);
        $sellingPrice = (float) ($data['selling_price'] ?? 0);

        if ($marginPercentage <= 0 && $purchasePrice > 0 && $sellingPrice > 0) {
            $data['margin_percentage'] = $service->calculateMarginPercentage($purchasePrice, $sellingPrice);
        }

        $data['is_manual_selling_price'] = false;

        return $data;
    }

    protected function afterSave(): void
    {
        app(BatchPricingSyncService::class)->syncBatchPricingToPurchaseItems($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.cancel.label'))
            ->url($this->getResource()::getUrl('index'))
            ->color('gray');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
