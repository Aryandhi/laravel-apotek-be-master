<?php

namespace App\Filament\Pages\Reports;

use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class StockMovementReport extends BaseReport
{
    protected static ?string $slug = 'reports/stock-movement';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Mutasi Stok';

    protected static ?int $navigationSort = 7;

    public ?string $productId = '';

    public ?string $type = '';

    protected function getReportTitle(): string
    {
        return 'Laporan Mutasi Stok';
    }

    protected function getAdditionalFilters(): array
    {
        return [
            Select::make('productId')
                ->label('Produk')
                ->options(fn () => ['' => 'Semua Produk'] + Product::active()->pluck('name', 'id')->toArray())
                ->default('')
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('type')
                ->label('Jenis Mutasi')
                ->options(fn () => ['' => 'Semua Jenis'] + collect(StockMovementType::cases())->mapWithKeys(fn ($type) => [$type->value => $type->label()])->toArray())
                ->default('')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ];
    }

    protected function getReportQuery(): Builder
    {
        return StockMovement::query()
            ->with(['productBatch.product', 'user'])
            ->whereBetween('created_at', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59'])
            ->when($this->productId, function ($query) {
                $query->whereHas('productBatch', function ($q) {
                    $q->where('product_id', $this->productId);
                });
            })
            ->when($this->type, function ($query) {
                $query->where('type', $this->type);
            });
    }

    protected function getReportColumns(): array
    {
        return [
            TextColumn::make('created_at')
                ->label('Tanggal')
                ->dateTime('d/m/Y H:i')
                ->sortable(),

            TextColumn::make('productBatch.product.code')
                ->label('Kode Produk')
                ->searchable(),

            TextColumn::make('productBatch.product.name')
                ->label('Nama Produk')
                ->searchable()
                ->wrap(),

            TextColumn::make('productBatch.batch_number')
                ->label('No. Batch')
                ->searchable(),

            TextColumn::make('type')
                ->label('Jenis')
                ->badge(),

            TextColumn::make('quantity')
                ->label('Qty')
                ->alignCenter()
                ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                ->formatStateUsing(fn ($state) => $state > 0 ? '+'.$state : $state),

            TextColumn::make('stock_before')
                ->label('Stok Sebelum')
                ->alignCenter(),

            TextColumn::make('stock_after')
                ->label('Stok Sesudah')
                ->alignCenter()
                ->weight('bold'),

            TextColumn::make('reference_type')
                ->label('Referensi')
                ->formatStateUsing(function ($state) {
                    if (! $state) {
                        return '-';
                    }

                    return class_basename($state);
                }),

            TextColumn::make('notes')
                ->label('Keterangan')
                ->wrap()
                ->limit(50),

            TextColumn::make('user.name')
                ->label('User')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getDefaultSort(): string
    {
        return 'created_at';
    }

    protected function getExportHeadings(): array
    {
        return [
            'Tanggal',
            'Kode Produk',
            'Nama Produk',
            'No. Batch',
            'Jenis',
            'Qty',
            'Stok Sebelum',
            'Stok Sesudah',
            'Referensi',
            'Keterangan',
            'User',
        ];
    }

    protected function getExportRow($record): array
    {
        return [
            $this->formatDateTime($record->created_at),
            $record->productBatch?->product?->code ?? '-',
            $record->productBatch?->product?->name ?? '-',
            $record->productBatch?->batch_number ?? '-',
            $record->type->label(),
            $record->quantity,
            $record->stock_before,
            $record->stock_after,
            $record->reference_type ? class_basename($record->reference_type) : '-',
            $record->notes ?? '-',
            $record->user?->name ?? '-',
        ];
    }

    protected function getSummaryData(): array
    {
        $query = $this->getReportQuery();

        $totalIn = (clone $query)->where('quantity', '>', 0)->sum('quantity');
        $totalOut = (clone $query)->where('quantity', '<', 0)->sum('quantity');
        $totalMovements = (clone $query)->count();

        return [
            'Total Mutasi' => number_format($totalMovements),
            'Stok Masuk' => '+'.number_format($totalIn),
            'Stok Keluar' => number_format($totalOut),
            'Net' => number_format($totalIn + $totalOut),
        ];
    }
}
