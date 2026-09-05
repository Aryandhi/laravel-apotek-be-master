<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Surat Pesanan')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('po_number')
                                    ->label('No. Surat Pesanan')
                                    ->disabled()
                                    ->dehydrated(false),
                                Placeholder::make('group_label')
                                    ->label('Tipe SP')
                                    ->content(fn ($record) => $record?->group?->label() ?? '-'),
                                Placeholder::make('supplier_name')
                                    ->label('Supplier')
                                    ->content(fn ($record) => $record?->supplier?->name ?? '-'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Surat Pesanan')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('order_date')
                                    ->label('Tanggal Pemesanan')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y'),
                            ]),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Daftar Item')
                    ->description('Ubah jumlah pesanan bila diperlukan. Produk tidak dapat ditambah/dihapus agar pemisahan golongan tetap sesuai regulasi.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Produk')
                                            ->relationship('product', 'name')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(2),
                                        Select::make('unit_id')
                                            ->label('Satuan')
                                            ->relationship('unit', 'name')
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                        TextInput::make('quantity')
                                            ->label('Jumlah Pesanan')
                                            ->numeric()
                                            ->required()
                                            ->minValue(1)
                                            ->columnSpan(1),
                                        TextInput::make('min_stock')
                                            ->label('Min. Stok')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->afterStateHydrated(function (TextInput $component, Get $get) {
                                                $component->state(Product::find($get('product_id'))?->min_stock);
                                            })
                                            ->columnSpan(1),
                                    ]),
                                Textarea::make('notes')
                                    ->label('Catatan Item')
                                    ->rows(1)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
