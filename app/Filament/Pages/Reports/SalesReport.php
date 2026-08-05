<?php

namespace App\Filament\Pages\Reports;

use App\Enums\SaleStatus;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\User;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class SalesReport extends BaseReport
{
    protected static ?string $slug = 'reports/sales';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?int $navigationSort = 2;

    public ?string $status = '';

    public ?string $userId = '';

    public ?string $paymentMethodId = '';

    protected function getReportTitle(): string
    {
        return 'Laporan Penjualan';
    }

    protected function getAdditionalFilters(): array
    {
        return [
            Select::make('status')
                ->label('Status')
                ->options([
                    '' => 'Semua Status',
                    'completed' => 'Selesai',
                    'pending' => 'Pending',
                    'cancelled' => 'Dibatalkan',
                ])
                ->default('')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('userId')
                ->label('Kasir')
                ->options(fn () => ['' => 'Semua Kasir'] + User::pluck('name', 'id')->toArray())
                ->default('')
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),

            Select::make('paymentMethodId')
                ->label('Metode Pembayaran')
                ->options(fn () => ['' => 'Semua Metode'] + PaymentMethod::query()->orderBy('name')->pluck('name', 'id')->toArray())
                ->default('')
                ->searchable()
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable()),
        ];
    }

    protected function getReportQuery(): Builder
    {
        return Sale::query()
            ->with(['customer', 'user', 'items', 'payments.paymentMethod'])
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->userId, function ($query) {
                $query->where('user_id', $this->userId);
            })
            ->when($this->paymentMethodId, function ($query) {
                $query->whereHas('payments', function ($paymentQuery) {
                    $paymentQuery->where('payment_method_id', $this->paymentMethodId);
                });
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

            TextColumn::make('customer.name')
                ->label('Pelanggan')
                ->default('-')
                ->searchable(),

            TextColumn::make('payment_method_names')
                ->label('Metode Pembayaran')
                ->getStateUsing(fn (Sale $record): string => $record->payments
                    ->pluck('paymentMethod.name')
                    ->filter()
                    ->unique()
                    ->implode(', ') ?: '-')
                ->wrap(),

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
                ->alignEnd()
                ->sortable(),

            TextColumn::make('total')
                ->label('Total')
                ->money('IDR')
                ->alignEnd()
                ->sortable()
                ->weight('bold'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->color(fn (SaleStatus $state) => $state->color())
                ->formatStateUsing(fn (SaleStatus $state) => $state->label())
                ->sortable(),

            TextColumn::make('user.name')
                ->label('Kasir')
                ->searchable(),
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
            'Pelanggan',
            'Metode Pembayaran',
            'Jumlah Item',
            'Subtotal',
            'Diskon',
            'Total',
            'Status',
            'Kasir',
        ];
    }

    protected function getExportRow($record): array
    {
        return [
            $record->invoice_number,
            $this->formatDate($record->date),
            $record->customer?->name ?? '-',
            $record->payments->pluck('paymentMethod.name')->filter()->unique()->implode(', ') ?: '-',
            $record->items->count(),
            $record->subtotal,
            $record->discount,
            $record->total,
            $record->status->value,
            $record->user?->name ?? '-',
        ];
    }

    protected function getSummaryData(): array
    {
        $query = $this->getReportQuery();

        $totalSales = (clone $query)->where('status', SaleStatus::Completed)->sum('total');
        $totalTransactions = (clone $query)->where('status', SaleStatus::Completed)->count();
        $totalDiscount = (clone $query)->where('status', SaleStatus::Completed)->sum('discount');

        return [
            'Total Penjualan' => $this->formatMoney($totalSales),
            'Jumlah Transaksi' => number_format($totalTransactions),
            'Total Diskon' => $this->formatMoney($totalDiscount),
            'Rata-rata/Transaksi' => $this->formatMoney($totalTransactions > 0 ? $totalSales / $totalTransactions : 0),
        ];
    }

    protected function getAdditionalPreviewFilterMap(): array
    {
        return [
            'status' => 'status',
            'user_id' => 'userId',
            'payment_method_id' => 'paymentMethodId',
        ];
    }
}
