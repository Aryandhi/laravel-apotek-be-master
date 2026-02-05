<?php

namespace App\Filament\Pages\Reports;

use App\Enums\PurchaseStatus;
use App\Models\Purchase;
use App\Models\Supplier;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class PurchaseReport extends BaseReport
{
    protected static ?string $slug = 'reports/purchase';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'Laporan Pembelian';

    protected static ?int $navigationSort = 5;

    public ?string $status = '';

    public ?string $supplierId = '';

    public ?string $paymentStatus = '';

    protected function getReportTitle(): string
    {
        return 'Laporan Pembelian';
    }

    protected function getAdditionalFilters(): array
    {
        return [
            Select::make('supplierId')
                ->label('Supplier')
                ->options(fn () => ['' => 'Semua Supplier'] + Supplier::pluck('name', 'id')->toArray())
                ->default('')
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('status')
                ->label('Status')
                ->options([
                    '' => 'Semua Status',
                    'draft' => 'Draft',
                    'ordered' => 'Dipesan',
                    'received' => 'Diterima',
                    'cancelled' => 'Dibatalkan',
                ])
                ->default('')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('paymentStatus')
                ->label('Pembayaran')
                ->options([
                    '' => 'Semua',
                    'paid' => 'Lunas',
                    'unpaid' => 'Belum Lunas',
                ])
                ->default('')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ];
    }

    protected function getReportQuery(): Builder
    {
        return Purchase::query()
            ->with(['supplier', 'user', 'items'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->supplierId, function ($query) {
                $query->where('supplier_id', $this->supplierId);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->paymentStatus === 'paid', function ($query) {
                $query->whereColumn('paid_amount', '>=', 'total');
            })
            ->when($this->paymentStatus === 'unpaid', function ($query) {
                $query->whereColumn('paid_amount', '<', 'total');
            });
    }

    protected function getReportColumns(): array
    {
        return [
            TextColumn::make('invoice_number')
                ->label('No. Invoice')
                ->searchable()
                ->sortable(),

            TextColumn::make('date')
                ->label('Tanggal')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('supplier.name')
                ->label('Supplier')
                ->searchable()
                ->sortable(),

            TextColumn::make('items_count')
                ->label('Items')
                ->counts('items')
                ->alignCenter(),

            TextColumn::make('subtotal')
                ->label('Subtotal')
                ->money('IDR')
                ->alignEnd()
                ->sortable(),

            TextColumn::make('discount')
                ->label('Diskon')
                ->money('IDR')
                ->alignEnd(),

            TextColumn::make('tax')
                ->label('Pajak')
                ->money('IDR')
                ->alignEnd(),

            TextColumn::make('total')
                ->label('Total')
                ->money('IDR')
                ->alignEnd()
                ->sortable()
                ->weight('bold'),

            TextColumn::make('paid_amount')
                ->label('Dibayar')
                ->money('IDR')
                ->alignEnd(),

            TextColumn::make('remaining')
                ->label('Sisa')
                ->getStateUsing(fn ($record) => $record->total - $record->paid_amount)
                ->money('IDR')
                ->alignEnd()
                ->color(fn ($state) => $state > 0 ? 'danger' : 'success'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (PurchaseStatus $state) => $state->color())
                ->formatStateUsing(fn (PurchaseStatus $state) => $state->label())
                ->sortable(),

            TextColumn::make('user.name')
                ->label('Dibuat')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    protected function getDefaultSort(): string
    {
        return 'date';
    }

    protected function getExportHeadings(): array
    {
        return [
            'No. Invoice',
            'Tanggal',
            'Supplier',
            'Jumlah Item',
            'Subtotal',
            'Diskon',
            'Pajak',
            'Total',
            'Dibayar',
            'Sisa',
            'Status',
        ];
    }

    protected function getExportRow($record): array
    {
        return [
            $record->invoice_number,
            $this->formatDate($record->date),
            $record->supplier?->name ?? '-',
            $record->items->count(),
            $record->subtotal,
            $record->discount,
            $record->tax,
            $record->total,
            $record->paid_amount,
            $record->total - $record->paid_amount,
            $record->status->value,
        ];
    }

    protected function getSummaryData(): array
    {
        $query = $this->getReportQuery();

        $totalPurchase = (clone $query)->sum('total');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalCount = (clone $query)->count();
        $unpaidCount = (clone $query)->whereColumn('paid_amount', '<', 'total')->count();

        return [
            'Total Pembelian' => $this->formatMoney($totalPurchase),
            'Total Dibayar' => $this->formatMoney($totalPaid),
            'Sisa Hutang' => $this->formatMoney($totalPurchase - $totalPaid),
            'Jumlah PO' => number_format($totalCount),
            'Belum Lunas' => number_format($unpaidCount),
        ];
    }
}
