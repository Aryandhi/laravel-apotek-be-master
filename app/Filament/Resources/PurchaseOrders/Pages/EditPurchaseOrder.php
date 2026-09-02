<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->status->isEditable()) {
            Notification::make()
                ->title('Surat Pesanan tidak dapat diedit')
                ->body('Hanya Surat Pesanan berstatus Draft yang dapat diedit.')
                ->warning()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
