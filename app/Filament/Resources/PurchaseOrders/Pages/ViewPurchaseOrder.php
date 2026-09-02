<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Surat Pesanan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('po_number')
                                    ->label('No. Surat Pesanan')
                                    ->copyable(),
                                TextEntry::make('group')
                                    ->label('Tipe SP')
                                    ->badge()
                                    ->formatStateUsing(fn ($state) => $state->label()),
                                TextEntry::make('status')
                                    ->label('Status')
                                    ->badge()
                                    ->color(fn ($state) => $state->color())
                                    ->formatStateUsing(fn ($state) => $state->label()),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Judul Surat Pesanan'),
                                TextEntry::make('supplier.name')
                                    ->label('Supplier'),
                                TextEntry::make('order_date')
                                    ->label('Tanggal Pemesanan')
                                    ->date('d M Y'),
                            ]),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Daftar Item')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('')
                            ->schema([
                                Grid::make(4)
                                    ->schema([
                                        TextEntry::make('product.name')
                                            ->label('Produk')
                                            ->columnSpan(2),
                                        TextEntry::make('unit.name')
                                            ->label('Satuan'),
                                        TextEntry::make('quantity')
                                            ->label('Jumlah Pesanan'),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
