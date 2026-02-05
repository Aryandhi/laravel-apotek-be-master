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

class TopProductsReport extends BaseReport
{
    protected static ?string $slug = 'reports/top-products';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?string $navigationLabel = 'Produk Terlaris';

    protected static ?int $navigationSort = 6;

    public ?string $categoryId = '';

    public ?string $sortBy = 'qty';

    protected function getReportTitle(): string
    {
        return 'Laporan Produk Terlaris';
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

            Select::make('sortBy')
                ->label('Urutkan')
                ->options([
                    'qty' => 'Qty Terjual',
                    'revenue' => 'Total Penjualan',
                    'profit' => 'Laba',
                ])
                ->default('qty')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ];
    }

    protected function getReportQuery(): Builder
    {
        $orderColumn = match ($this->sortBy) {
            'revenue' => 'total_sales',
            'profit' => 'gross_profit',
            default => 'total_qty',
        };

        return SaleItem::query()
            ->select([
                'products.id as product_id',
                'products.code as product_code',
                'products.name as product_name',
                DB::raw('COALESCE(categories.name, "-") as category_name'),
                DB::raw('COALESCE(units.name, "pcs") as unit_name'),
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_sales'),
                DB::raw('SUM(sale_items.quantity * COALESCE(product_batches.purchase_price, 0)) as total_cost'),
                DB::raw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * COALESCE(product_batches.purchase_price, 0)) as gross_profit'),
            ])
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('units', 'products.base_unit_id', '=', 'units.id')
            ->leftJoin('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', SaleStatus::Completed)
            ->whereBetween('sales.date', [$this->startDate, $this->endDate])
            ->when($this->categoryId, function ($query) {
                $query->where('products.category_id', $this->categoryId);
            })
            ->groupBy('products.id', 'products.code', 'products.name', 'categories.name', 'units.name')
            ->orderByDesc($orderColumn);
    }

    protected function getReportColumns(): array
    {
        return [
            TextColumn::make('row_number')
                ->label('#')
                ->rowIndex()
                ->alignCenter(),

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
                ->sortable()
                ->weight('bold'),

            TextColumn::make('unit_name')
                ->label('Satuan'),

            TextColumn::make('total_sales')
                ->label('Total Penjualan')
                ->money('IDR')
                ->alignEnd()
                ->sortable(),

            TextColumn::make('total_cost')
                ->label('HPP')
                ->money('IDR')
                ->alignEnd(),

            TextColumn::make('gross_profit')
                ->label('Laba')
                ->money('IDR')
                ->alignEnd()
                ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),

            TextColumn::make('margin')
                ->label('Margin')
                ->getStateUsing(function ($record) {
                    if ($record->total_sales <= 0) {
                        return 0;
                    }

                    return round(($record->gross_profit / $record->total_sales) * 100, 1);
                })
                ->suffix('%')
                ->alignCenter(),
        ];
    }

    protected function getDefaultSort(): string
    {
        return 'total_qty';
    }

    protected function getTableRecordKeyName(): string
    {
        return 'product_id';
    }

    protected function getExportHeadings(): array
    {
        return [
            'No',
            'Kode',
            'Nama Produk',
            'Kategori',
            'Qty Terjual',
            'Satuan',
            'Total Penjualan',
            'HPP',
            'Laba',
            'Margin %',
        ];
    }

    protected function getExportRow($record): array
    {
        static $rowNumber = 0;
        $rowNumber++;

        $margin = $record->total_sales > 0
            ? round(($record->gross_profit / $record->total_sales) * 100, 1)
            : 0;

        return [
            $rowNumber,
            $record->product_code,
            $record->product_name,
            $record->category_name ?? '-',
            $record->total_qty,
            $record->unit_name ?? '-',
            $record->total_sales,
            $record->total_cost,
            $record->gross_profit,
            $margin.'%',
        ];
    }

    protected function getSummaryData(): array
    {
        $data = $this->getReportQuery()
            ->getQuery()
            ->selectRaw('
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.subtotal) as total_sales,
                COUNT(DISTINCT sale_items.product_id) as product_count
            ')
            ->first();

        return [
            'Total Produk' => number_format($data->product_count ?? 0),
            'Total Qty Terjual' => number_format($data->total_qty ?? 0),
            'Total Penjualan' => $this->formatMoney($data->total_sales ?? 0),
        ];
    }
}
