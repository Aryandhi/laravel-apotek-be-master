<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Data identitas produk')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Kode Produk')
                                    ->placeholder('cth: PRD001')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('barcode')
                                    ->label('Barcode')
                                    ->placeholder('cth: 8991234567890')
                                    ->maxLength(50),
                                TextInput::make('kfa_code')
                                    ->label('Kode KFA')
                                    ->placeholder('cth: 12345678')
                                    ->helperText('Kode Farmasi Apotek dari BPOM')
                                    ->maxLength(50),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Produk')
                                    ->placeholder('cth: Paracetamol 500mg')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('generic_name')
                                    ->label('Nama Generik')
                                    ->placeholder('cth: Paracetamol')
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Kategori & Satuan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('base_unit_id')
                                    ->label('Satuan Dasar')
                                    ->relationship('baseUnit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->helperText('Satuan terkecil untuk produk ini'),
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

                Section::make('Pengaturan Stok')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_stock')
                                    ->label('Stok Minimum')
                                    ->required()
                                    ->numeric()
                                    ->default(10)
                                    ->minValue(0)
                                    ->helperText('Alert jika stok di bawah ini'),
                                TextInput::make('max_stock')
                                    ->label('Stok Maksimum')
                                    ->numeric()
                                    ->minValue(0)
                                    ->helperText('Batas atas penyimpanan'),
                                TextInput::make('rack_location')
                                    ->label('Lokasi Rak')
                                    ->placeholder('cth: A1-02')
                                    ->maxLength(50),
                            ]),
                    ]),

                Section::make('Pengaturan Lanjutan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('requires_prescription')
                                    ->label('Memerlukan Resep')
                                    ->helperText('Aktifkan jika produk ini harus dengan resep dokter')
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->helperText('Produk tidak aktif tidak akan muncul di POS')
                                    ->default(true),
                            ]),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Deskripsi produk, komposisi, indikasi, dll')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('image')
                            ->label('Gambar Produk')
                            ->image()
                            ->disk('public')
                            ->directory('products')
                            ->visibility('public')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
