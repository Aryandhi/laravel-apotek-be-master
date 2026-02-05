<?php

namespace App\Filament\Resources\XenditTransactions\Pages;

use App\Enums\XenditPaymentStatus;
use App\Filament\Resources\XenditTransactions\XenditTransactionResource;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewXenditTransaction extends ViewRecord
{
    protected static string $resource = XenditTransactionResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('external_id')
                                    ->label('External ID')
                                    ->copyable(),

                                TextEntry::make('xendit_id')
                                    ->label('Xendit ID')
                                    ->copyable()
                                    ->placeholder('-'),

                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn (XenditPaymentStatus $state): string => match ($state) {
                                        XenditPaymentStatus::Pending => 'warning',
                                        XenditPaymentStatus::Paid, XenditPaymentStatus::Settled => 'success',
                                        XenditPaymentStatus::Expired => 'gray',
                                        XenditPaymentStatus::Failed => 'danger',
                                    }),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextEntry::make('amount')
                                    ->label('Jumlah')
                                    ->money('IDR'),

                                TextEntry::make('payment_method')
                                    ->label('Metode Pembayaran')
                                    ->badge(),

                                TextEntry::make('payment_channel')
                                    ->label('Channel')
                                    ->badge()
                                    ->placeholder('-'),
                            ]),
                    ]),

                Section::make('Penjualan Terkait')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('sale.invoice_number')
                                    ->label('Nomor Invoice')
                                    ->url(fn ($record) => $record->sale_id
                                        ? route('filament.admin.resources.sales.edit', $record->sale_id)
                                        : null
                                    )
                                    ->placeholder('-'),

                                TextEntry::make('sale.customer.name')
                                    ->label('Pelanggan')
                                    ->placeholder('Umum'),

                                TextEntry::make('sale.total')
                                    ->label('Total Penjualan')
                                    ->money('IDR')
                                    ->placeholder('-'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->sale_id !== null),

                Section::make('Waktu')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->dateTime('d M Y H:i:s'),

                                TextEntry::make('expires_at')
                                    ->label('Kedaluwarsa')
                                    ->dateTime('d M Y H:i:s')
                                    ->placeholder('-'),

                                TextEntry::make('paid_at')
                                    ->label('Waktu Bayar')
                                    ->dateTime('d M Y H:i:s')
                                    ->placeholder('-'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir Update')
                                    ->dateTime('d M Y H:i:s'),
                            ]),
                    ]),

                Section::make('Invoice URL')
                    ->schema([
                        TextEntry::make('invoice_url')
                            ->label('URL')
                            ->url(fn ($record) => $record->invoice_url)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                    ])
                    ->visible(fn ($record) => $record->invoice_url !== null),

                Section::make('Response Xendit')
                    ->schema([
                        TextEntry::make('xendit_response')
                            ->label('')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : null)
                            ->copyable()
                            ->placeholder('Tidak ada data'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
