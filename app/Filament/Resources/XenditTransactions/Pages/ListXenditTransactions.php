<?php

namespace App\Filament\Resources\XenditTransactions\Pages;

use App\Filament\Resources\XenditTransactions\XenditTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListXenditTransactions extends ListRecords
{
    protected static string $resource = XenditTransactionResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\XenditTransactions\Widgets\XenditStatsWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
