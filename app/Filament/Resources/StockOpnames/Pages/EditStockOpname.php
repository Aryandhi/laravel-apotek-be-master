<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockOpname extends EditRecord
{
    protected static string $resource = StockOpnameResource::class;

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

    /**
     * The product/rack filters only change what's visible in the repeater. A relationship
     * Repeater persists directly from its own live state (before mutateFormDataBeforeSave
     * ever runs), so the full item list must be restored here, right before validation/save.
     */
    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->restoreFullItemsFromSource();

        parent::save($shouldRedirect, $shouldSendSavedNotification);
    }

    protected function restoreFullItemsFromSource(): void
    {
        $itemsSource = $this->data['items_source'] ?? null;

        if (is_array($itemsSource)) {
            $this->data['items'] = $itemsSource;
        }
    }
}
