<?php

namespace App\Filament\Pages\Reports;

use App\Enums\BatchStatus;
use App\Models\Category;
use App\Models\ProductBatch;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class StockReport extends BaseReport
{
    protected static ?string $slug = 'reports/stock';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Laporan Stok';

    protected static ?int $navigationSort = 3;

    public ?string $stockFilter = '';

    public ?string $categoryId = '';

    protected function getReportTitle(): string
    {
        return 'Laporan Stok';
    }

    protected function getAdditionalFilters(): array
    {
        return [
            Select::make('stockFilter')
                ->label('Filter Stok')
                ->options([
                    '' => 'Semua',
                    'low' => 'Stok Menipis (< 10)',
                    'out' => 'Stok Habis',
                    'expiring' => 'Hampir Kadaluarsa (30 hari)',
                    'expired' => 'Sudah Kadaluarsa',
                ])
                ->default('')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('categoryId')
                ->label('Kategori')
                ->options(fn () => ['' => 'Semua Kategori'] + Category::pluck('name', 'id')->toArray())
                ->default('')
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ];
    }

    protected function getReportQuery(): Builder
    {
        return ProductBatch::query()
            ->with(['product', 'product.category', 'product.baseUnit', 'supplier'])
            ->when($this->stockFilter === 'low', function ($query) {
                $query->where('stock', '>', 0)->where('stock', '<', 10);
            })
            ->when($this->stockFilter === 'out', function ($query) {
                $query->where('stock', '<=', 0);
            })
            ->when($this->stockFilter === 'expiring', function ($query) {
                $query->where('stock', '>', 0)
                    ->whereBetween('expired_date', [now(), now()->addDays(30)]);
            })
            ->when($this->stockFilter === 'expired', function ($query) {
                $query->where('expired_date', '<', now());
            })
            ->when($this->stockFilter === '', function ($query) {
                $query->where('stock', '>', 0);
            })
            ->when($this->categoryId, function ($query) {
                $query->whereHas('product', function ($q) {
                    $q->where('category_id', $this->categoryId);
                });
            });
    }

    protected function getReportColumns(): array
    {
        return [
            TextColumn::make('product.code')
                ->label('Kode')
                ->searchable()
                ->sortable(),

            TextColumn::make('product.name')
                ->label('Nama Produk')
                ->searchable()
                ->sortable()
                ->wrap(),

            TextColumn::make('product.category.name')
                ->label('Kategori')
                ->sortable(),

            TextColumn::make('batch_number')
                ->label('No. Batch')
                ->searchable(),

            TextColumn::make('expired_date')
                ->label('Kadaluarsa')
                ->date('d/m/Y')
                ->sortable()
                ->color(fn ($record) => $record->isExpired() ? 'danger' : ($record->isNearExpired(30) ? 'warning' : null)),

            TextColumn::make('stock')
                ->label('Stok')
                ->alignCenter()
                ->sortable()
                ->color(fn ($state) => $state <= 0 ? 'danger' : ($state < 10 ? 'warning' : 'success')),

            TextColumn::make('product.baseUnit.name')
                ->label('Satuan'),

            TextColumn::make('purchase_price')
                ->label('Harga Beli')
                ->money('IDR')
                ->alignEnd(),

            TextColumn::make('selling_price')
                ->label('Harga Jual')
                ->money('IDR')
                ->alignEnd(),

            TextColumn::make('stock_value')
                ->label('Nilai Stok')
                ->getStateUsing(fn ($record) => $record->stock * $record->purchase_price)
                ->money('IDR')
                ->alignEnd()
                ->weight('bold'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (BatchStatus $state) => $state->color())
                ->formatStateUsing(fn (BatchStatus $state) => $state->label()),
        ];
    }

    protected function getDefaultSort(): string
    {
        return 'expired_date';
    }

    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    protected function getExportHeadings(): array
    {
        return [
            'Kode',
            'Nama Produk',
            'Kategori',
            'No. Batch',
            'Kadaluarsa',
            'Stok',
            'Satuan',
            'Harga Beli',
            'Harga Jual',
            'Nilai Stok',
            'Status',
        ];
    }

    protected function getExportRow($record): array
    {
        return [
            $record->product?->code,
            $record->product?->name,
            $record->product?->category?->name ?? '-',
            $record->batch_number,
            $this->formatDate($record->expired_date),
            $record->stock,
            $record->product?->baseUnit?->name ?? '-',
            $record->purchase_price,
            $record->selling_price,
            $record->stock * $record->purchase_price,
            $record->status->value,
        ];
    }

    protected function getSummaryData(): array
    {
        $query = ProductBatch::query()->where('stock', '>', 0);

        $totalValue = (clone $query)->selectRaw('SUM(stock * purchase_price) as total')->value('total') ?? 0;
        $totalItems = (clone $query)->sum('stock');
        $totalBatches = (clone $query)->count();
        $lowStock = (clone $query)->where('stock', '<', 10)->count();
        $expiring = (clone $query)->whereBetween('expired_date', [now(), now()->addDays(30)])->count();
        $expired = ProductBatch::where('expired_date', '<', now())->where('stock', '>', 0)->count();

        return [
            'Total Nilai Stok' => $this->formatMoney($totalValue),
            'Total Items' => number_format($totalItems),
            'Jumlah Batch' => number_format($totalBatches),
            'Stok Menipis' => number_format($lowStock),
            'Hampir Kadaluarsa' => number_format($expiring),
            'Sudah Kadaluarsa' => number_format($expired),
        ];
    }
}
