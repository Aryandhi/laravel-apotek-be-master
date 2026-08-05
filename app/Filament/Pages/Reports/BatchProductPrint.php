<?php

namespace App\Filament\Pages\Reports;

use App\Models\Product;
use App\Models\ProductBatch;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class BatchProductPrint extends BaseReport
{
    protected static ?string $slug = 'inventory/batch-products';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static ?string $navigationLabel = 'Cetak Batch Produk';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 5;

    public ?string $productId = '';

    public ?string $rackLocation = '';

    public function mount(): void
    {
        parent::mount();

        $this->startDate = null;
        $this->endDate = null;
    }

    public function getReportTitle(): string
    {
        return 'Cetak Batch Produk';
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter')
                    ->schema([
                        Grid::make()
                            ->schema([
                                ...$this->getAdditionalFilters(),
                            ])
                            ->columns([
                                'default' => 2,
                                'sm' => 2,
                                'md' => 4,
                                'lg' => 4,
                                'xl' => 4,
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Data '.$this->getReportTitle())
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
            ]);
    }

    protected function getAdditionalFilters(): array
    {
        return [
            Select::make('productId')
                ->label('Nama Produk')
                ->options(fn () => ['' => 'Semua Produk'] + Product::active()->orderBy('name')->pluck('name', 'id')->toArray())
                ->searchable()
                ->placeholder('Semua Produk')
                ->live(onBlur: true)
                ->afterStateUpdated(fn () => $this->resetTable())
                ->default(''),

            Select::make('rackLocation')
                ->label('Lokasi Rak')
                ->options(fn () => ['' => 'Semua Lokasi Rak'] + Product::query()
                    ->whereNotNull('rack_location')
                    ->where('rack_location', '!=', '')
                    ->distinct()
                    ->orderBy('rack_location')
                    ->pluck('rack_location', 'rack_location')
                    ->toArray())
                ->searchable()
                ->placeholder('Semua Lokasi Rak')
                ->live(onBlur: true)
                ->afterStateUpdated(fn () => $this->resetTable())
                ->default(''),
        ];
    }

    protected function getReportQuery(): Builder
    {
        return ProductBatch::query()
            ->with(['product'])
            ->when($this->productId, function (Builder $query) {
                $query->where('product_id', $this->productId);
            })
            ->when($this->rackLocation, function (Builder $query) {
                $query->whereHas('product', function (Builder $query) {
                    $query->where('rack_location', $this->rackLocation);
                });
            });
    }

    protected function getReportColumns(): array
    {
        return [
            TextColumn::make('product.name')
                ->label('Nama Produk')
                ->searchable()
                ->sortable()
                ->wrap(),

            TextColumn::make('batch_number')
                ->label('No. Batch')
                ->searchable()
                ->sortable(),

            TextColumn::make('product.rack_location')
                ->label('Lokasi Rak')
                ->sortable()
                ->toggleable(),

            TextColumn::make('expired_date')
                ->label('Kadaluarsa')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('physical_stock')
                ->label('Stok Fisik')
                ->getStateUsing(fn ($record) => '')
                ->sortable(false)
                ->toggleable(),

            TextColumn::make('notes')
                ->label('Catatan')
                ->getStateUsing(fn ($record) => $record->product?->description ?? '-')
                ->wrap()
                ->toggleable(),
        ];
    }

    public function getExportHeadings(): array
    {
        return [
            'Nama Produk',
            'No. Batch',
            'Lokasi Rak',
            'Kadaluarsa',
            'Stok Fisik',
            'Catatan',
        ];
    }

    public function getExportRow($record): array
    {
        return [
            $record->product?->name,
            $record->batch_number,
            $record->product?->rack_location,
            $this->formatDate($record->expired_date),
            '',
            $record->product?->description ?? '-',
        ];
    }

    public function getSummaryData(): array
    {
        $query = $this->getReportQuery();

        return [
            'Total Batch' => number_format($query->count()),
        ];
    }

    public function exportPdf()
    {
        $filename = $this->getExportFilename('pdf');

        $pdf = Pdf::loadView('exports.batch-product-print', [
            'title' => $this->getReportTitle(),
            'period' => $this->startDate.' - '.$this->endDate,
            'headings' => $this->getExportHeadings(),
            'data' => $this->getExportData(),
            'summary' => $this->getSummaryData(),
            'storeName' => config('app.name'),
            'printDate' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            $filename
        );
    }

    protected function getHeaderActions(): array
    {
        $actions = parent::getHeaderActions();

        return array_map(function ($action) {
            if ($action->getName() === 'print') {
                return $action
                    ->url($this->getPreviewUrl())
                    ->openUrlInNewTab();
            }

            return $action;
        }, $actions);
    }

    public function printReport()
    {
        return redirect()->to($this->getPreviewUrl());
    }

    protected function getPreviewUrl(): string
    {
        $queryString = http_build_query([
            'product_id' => $this->productId ?? '',
            'rack_location' => $this->rackLocation ?? '',
        ]);

        return route('reports.batch-products.preview').($queryString ? '?'.$queryString : '');
    }

    protected function getDefaultSort(): string
    {
        return 'product.name';
    }

    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }
}
