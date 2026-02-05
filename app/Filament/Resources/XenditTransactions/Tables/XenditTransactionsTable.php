<?php

namespace App\Filament\Resources\XenditTransactions\Tables;

use App\Enums\XenditPaymentStatus;
use App\Models\XenditTransaction;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Response;

class XenditTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $transactions = XenditTransaction::with('sale')
                            ->orderBy('created_at', 'desc')
                            ->get();

                        $csv = "External ID,Invoice Penjualan,Jumlah,Metode,Channel,Status,Waktu Bayar,Dibuat\n";

                        foreach ($transactions as $tx) {
                            $csv .= sprintf(
                                "%s,%s,%s,%s,%s,%s,%s,%s\n",
                                $tx->external_id,
                                $tx->sale?->invoice_number ?? '-',
                                $tx->amount,
                                $tx->payment_method ?? '-',
                                $tx->payment_channel ?? '-',
                                $tx->status?->value ?? '-',
                                $tx->paid_at?->format('Y-m-d H:i:s') ?? '-',
                                $tx->created_at?->format('Y-m-d H:i:s') ?? '-'
                            );
                        }

                        $filename = 'xendit-transactions-'.now()->format('Y-m-d-His').'.csv';

                        return Response::streamDownload(function () use ($csv) {
                            echo $csv;
                        }, $filename, [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),
            ])
            ->columns([
                TextColumn::make('external_id')
                    ->label('External ID')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('External ID berhasil disalin'),

                TextColumn::make('sale.invoice_number')
                    ->label('Invoice Penjualan')
                    ->searchable()
                    ->url(fn ($record) => $record->sale_id
                        ? route('filament.admin.resources.sales.edit', $record->sale_id)
                        : null
                    ),

                TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'QRIS' => 'success',
                        'EWALLET' => 'info',
                        'VIRTUAL_ACCOUNT' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('payment_channel')
                    ->label('Channel')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (XenditPaymentStatus $state): string => match ($state) {
                        XenditPaymentStatus::Pending => 'warning',
                        XenditPaymentStatus::Paid, XenditPaymentStatus::Settled => 'success',
                        XenditPaymentStatus::Expired => 'gray',
                        XenditPaymentStatus::Failed => 'danger',
                    }),

                TextColumn::make('paid_at')
                    ->label('Waktu Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('expires_at')
                    ->label('Kedaluwarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(XenditPaymentStatus::class),

                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'QRIS' => 'QRIS',
                        'EWALLET' => 'E-Wallet',
                        'VIRTUAL_ACCOUNT' => 'Virtual Account',
                    ]),

                Filter::make('paid')
                    ->label('Sudah Dibayar')
                    ->query(fn (Builder $query): Builder => $query->whereIn('status', [
                        XenditPaymentStatus::Paid,
                        XenditPaymentStatus::Settled,
                    ])),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Dari: '.\Carbon\Carbon::parse($data['from'])->format('d M Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Sampai: '.\Carbon\Carbon::parse($data['until'])->format('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->emptyStateHeading('Belum ada transaksi Xendit')
            ->emptyStateDescription('Transaksi Xendit akan muncul di sini setelah pelanggan melakukan pembayaran via QRIS atau E-Wallet.')
            ->emptyStateIcon('heroicon-o-credit-card');
    }
}
