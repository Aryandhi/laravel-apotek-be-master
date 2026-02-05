<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Enums\SaleStatus;
use App\Models\SaleItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Reactive;

class TopProductsWidget extends TableWidget
{
    protected static ?string $heading = 'Produk Terlaris';

    protected int|string|array $columnSpan = 1;

    #[Reactive]
    public ?string $startDate = null;

    #[Reactive]
    public ?string $endDate = null;

    public function table(Table $table): Table
    {
        $startDate = $this->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->endDate ?? now()->format('Y-m-d');

        return $table
            ->query(
                SaleItem::query()
                    ->select([
                        'sale_items.product_id',
                        DB::raw('SUM(sale_items.quantity) as total_qty'),
                        DB::raw('SUM(sale_items.subtotal) as total_sales'),
                    ])
                    ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
                    ->whereBetween('sales.date', [$startDate, $endDate])
                    ->where('sales.status', SaleStatus::Completed)
                    ->groupBy('sale_items.product_id')
                    ->orderByDesc('total_qty')
                    ->limit(5)
                    ->with('product:id,name,code')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->description(fn ($record) => $record->product?->code)
                    ->searchable(),

                TextColumn::make('total_qty')
                    ->label('Qty')
                    ->alignEnd()
                    ->suffix(' pcs'),

                TextColumn::make('total_sales')
                    ->label('Total')
                    ->money('IDR')
                    ->alignEnd(),
            ])
            ->paginated(false);
    }

    public function getTableRecordKey($record): string
    {
        return (string) ($record->product_id ?? uniqid());
    }
}
