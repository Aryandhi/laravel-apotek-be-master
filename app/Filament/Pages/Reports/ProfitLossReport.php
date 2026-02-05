<?php

namespace App\Filament\Pages\Reports;

use App\Enums\SaleStatus;
use App\Models\Category;
use App\Models\SaleItem;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfitLossReport extends BaseReport
{
    protected static ?string $slug = 'reports/profit-loss';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Laporan Laba Rugi';

    protected static ?int $navigationSort = 4;

    public ?string $categoryId = '';

    public ?string $groupBy = 'product';

    protected function getReportTitle(): string
    {
        return 'Laporan Laba Rugi';
    }

    protected function getAdditionalFilters(): array
    {
        return [
            Select::make('categoryId')
                ->label('Kategori')
                ->options(fn () => ['' => 'Semua Kategori'] + Category::pluck('name', 'id')->toArray())
                ->default('')
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('groupBy')
                ->label('Kelompokkan')
                ->options([
                    'product' => 'Per Produk',
                    'category' => 'Per Kategori',
                    'daily' => 'Per Hari',
                ])
                ->default('product')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ];
    }

    protected function getReportQuery(): Builder
    {
        return SaleItem::query()
            ->select([
                'products.id as product_id',
                'products.code as product_code',
                'products.name as product_name',
                DB::raw('COALESCE(categories.name, "-") as category_name'),
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_sales'),
                DB::raw('COALESCE(SUM(sale_items.quantity * product_batches.purchase_price), 0) as total_cost'),
                DB::raw('SUM(sale_items.subtotal) - COALESCE(SUM(sale_items.quantity * product_batches.purchase_price), 0) as gross_profit'),
            ])
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', SaleStatus::Completed)
            ->whereBetween('sales.date', [$this->startDate, $this->endDate])
            ->when($this->categoryId, function ($query) {
                $query->where('products.category_id', $this->categoryId);
            })
            ->groupBy('products.id', 'products.code', 'products.name', 'categories.name');
    }

    protected function getReportColumns(): array
    {
        return [
            TextColumn::make('product_code')
                ->label('Kode')
                ->searchable()
                ->sortable(),

            TextColumn::make('product_name')
                ->label('Nama Produk')
                ->searchable()
                ->sortable()
                ->wrap(),

            TextColumn::make('category_name')
                ->label('Kategori')
                ->sortable(),

            TextColumn::make('total_qty')
                ->label('Qty Terjual')
                ->alignCenter()
                ->sortable(),

            TextColumn::make('total_sales')
                ->label('Penjualan')
                ->money('IDR')
                ->alignEnd()
                ->sortable(),

            TextColumn::make('total_cost')
                ->label('HPP')
                ->money('IDR')
                ->alignEnd()
                ->sortable(),

            TextColumn::make('gross_profit')
                ->label('Laba Kotor')
                ->money('IDR')
                ->alignEnd()
                ->sortable()
                ->color(fn ($state) => $state >= 0 ? 'success' : 'danger')
                ->weight('bold'),

            TextColumn::make('margin')
                ->label('Margin %')
                ->getStateUsing(function ($record) {
                    if ($record->total_sales <= 0) {
                        return 0;
                    }

                    return round(($record->gross_profit / $record->total_sales) * 100, 1);
                })
                ->suffix('%')
                ->alignCenter()
                ->color(fn ($state) => $state >= 20 ? 'success' : ($state >= 10 ? 'warning' : 'danger')),
        ];
    }

    protected function getDefaultSort(): string
    {
        return 'gross_profit';
    }

    protected function getTableRecordKeyName(): string
    {
        return 'product_id';
    }

    protected function getExportHeadings(): array
    {
        return [
            'Kode',
            'Nama Produk',
            'Kategori',
            'Qty Terjual',
            'Penjualan',
            'HPP',
            'Laba Kotor',
            'Margin %',
        ];
    }

    protected function getExportRow($record): array
    {
        $margin = $record->total_sales > 0
            ? round(($record->gross_profit / $record->total_sales) * 100, 1)
            : 0;

        return [
            $record->product_code,
            $record->product_name,
            $record->category_name ?? '-',
            $record->total_qty,
            $record->total_sales,
            $record->total_cost,
            $record->gross_profit,
            $margin.'%',
        ];
    }

    protected function getSummaryData(): array
    {
        $data = SaleItem::query()
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', SaleStatus::Completed)
            ->whereBetween('sales.date', [$this->startDate, $this->endDate])
            ->when($this->categoryId, function ($query) {
                $query->where('products.category_id', $this->categoryId);
            })
            ->selectRaw('
                SUM(sale_items.subtotal) as total_sales,
                SUM(sale_items.quantity * product_batches.purchase_price) as total_cost,
                SUM(sale_items.subtotal) - SUM(sale_items.quantity * product_batches.purchase_price) as gross_profit,
                SUM(sale_items.quantity) as total_qty
            ')
            ->first();

        $margin = ($data->total_sales ?? 0) > 0
            ? round((($data->gross_profit ?? 0) / $data->total_sales) * 100, 1)
            : 0;

        return [
            'Total Penjualan' => $this->formatMoney($data->total_sales ?? 0),
            'Total HPP' => $this->formatMoney($data->total_cost ?? 0),
            'Laba Kotor' => $this->formatMoney($data->gross_profit ?? 0),
            'Margin' => $margin.'%',
            'Qty Terjual' => number_format($data->total_qty ?? 0),
        ];
    }
}
