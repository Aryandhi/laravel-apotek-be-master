<?php

namespace App\Filament\Resources\ProductBatches\Schemas;

use App\Enums\BatchStatus;
use App\Models\Purchase;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductBatchForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Batch')
                    ->description('Data identitas batch produk')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('batch_number')
                                    ->label('Nomor Batch')
                                    ->placeholder('cth: BTH-2024-001')
                                    ->required()
                                    ->maxLength(100),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('expired_date')
                                    ->label('Tanggal Kadaluarsa')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->minDate(now()),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(BatchStatus::class)
                                    ->default('active')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Harga')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('purchase_price')
                                    ->label('Harga Beli')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->minValue(0),
                                TextInput::make('selling_price')
                                    ->label('Harga Jual')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->minValue(0),
                            ]),
                    ]),

                Section::make('Stok')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('initial_stock')
                                    ->label('Stok Awal')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Jumlah stok saat batch masuk'),
                                TextInput::make('stock')
                                    ->label('Stok Saat Ini')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Jumlah stok tersedia sekarang'),
                            ]),
                    ]),

                Section::make('Referensi')
                    ->description('Informasi pembelian terkait batch ini')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih supplier'),
                                Select::make('purchase_id')
                                    ->label('Pembelian')
                                    ->options(fn () => Purchase::query()
                                        ->latest()
                                        ->limit(50)
                                        ->get()
                                        ->mapWithKeys(fn ($purchase) => [
                                            $purchase->id => "#{$purchase->invoice_number} - {$purchase->date->format('d M Y')}",
                                        ])
                                        ->toArray()
                                    )
                                    ->searchable()
                                    ->placeholder('Pilih pembelian'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
