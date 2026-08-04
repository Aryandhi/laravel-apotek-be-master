<?php

namespace App\Filament\Resources\ProductBatches\Pages;

use App\Filament\Resources\ProductBatches\ProductBatchResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductBatches extends ListRecords
{
    protected static string $resource = ProductBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(ProductBatchResource::getUrl('import'))
                ->color('success'),
            CreateAction::make(),
        ];
    }
}
