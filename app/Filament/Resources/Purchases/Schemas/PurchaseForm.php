<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Enums\PurchaseStatus;
use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembelian')
                    ->description('Data dasar pembelian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('No. Invoice')
                                    ->required()
                                    ->placeholder('cth: INV-2026-001')
                                    ->maxLength(100),
                                DatePicker::make('date')
                                    ->label('Tanggal')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->default(now()),
                                DatePicker::make('due_date')
                                    ->label('Jatuh Tempo')
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->helperText('Tanggal jatuh tempo pembayaran'),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->placeholder('Pilih supplier'),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(PurchaseStatus::class)
                                    ->default('draft')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Daftar Barang')
                    ->description('Tambahkan produk yang dibeli')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Produk')
                                            ->options(fn () => Product::query()
                                                ->where('is_active', true)
                                                ->orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn ($product) => [
                                                    $product->id => "{$product->name} ({$product->code})",
                                                ])
                                                ->toArray()
                                            )
                                            ->searchable()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                                if ($state) {
                                                    $product = Product::find($state);
                                                    if ($product) {
                                                        $set('purchase_price', $product->purchase_price);
                                                        $set('selling_price', $product->selling_price);
                                                        $set('unit_id', $product->base_unit_id);
                                                        self::calculateItemTotal($set, $get);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get))
                                            ->columnSpan(1),
                                        Select::make('unit_id')
                                            ->label('Satuan')
                                            ->relationship('unit', 'name')
                                            ->required()
                                            ->preload()
                                            ->columnSpan(1),
                                        TextInput::make('purchase_price')
                                            ->label('Harga Beli')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get))
                                            ->columnSpan(1),
                                        TextInput::make('total')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(0)
                                            ->columnSpan(1),
                                    ]),
                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('batch_number')
                                            ->label('No. Batch')
                                            ->placeholder('cth: BTH-001')
                                            ->maxLength(100),
                                        DatePicker::make('expired_date')
                                            ->label('Tgl Kadaluarsa')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->minDate(now()),
                                        TextInput::make('selling_price')
                                            ->label('Harga Jual')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->helperText('Harga jual untuk batch ini'),
                                        TextInput::make('discount')
                                            ->label('Diskon')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get)),
                                    ]),
                                Hidden::make('subtotal')
                                    ->default(0),
                                Hidden::make('received_quantity')
                                    ->default(0),
                            ])
                            ->columns(1)
                            ->addActionLabel('Tambah Barang')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['product_id']
                                ? Product::find($state['product_id'])?->name.' - Qty: '.($state['quantity'] ?? 0)
                                : 'Barang Baru'
                            )
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateTotals($set, $get))
                            ->deleteAction(
                                fn ($action) => $action->after(fn (Set $set, Get $get) => self::calculateTotals($set, $get))
                            ),
                    ]),

                Section::make('Rincian Biaya')
                    ->description('Detail perhitungan biaya pembelian')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                                TextInput::make('discount')
                                    ->label('Diskon')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateGrandTotal($set, $get)),
                                TextInput::make('tax')
                                    ->label('Pajak')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->reactive()
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateGrandTotal($set, $get)),
                                TextInput::make('total')
                                    ->label('Total')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('paid_amount')
                                    ->label('Sudah Dibayar')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->reactive(),
                                Placeholder::make('remaining_display')
                                    ->label('Sisa Pembayaran')
                                    ->content(function ($get) {
                                        $total = floatval($get('total') ?? 0);
                                        $paid = floatval($get('paid_amount') ?? 0);
                                        $remaining = $total - $paid;

                                        return 'Rp '.number_format($remaining, 0, ',', '.');
                                    }),
                            ]),
                    ]),

                Section::make('Informasi Tambahan')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->placeholder('Catatan tambahan untuk pembelian ini...')
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('user_id')
                            ->label('Dibuat Oleh')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->default(fn () => auth()->id())
                            ->dehydrated(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    private static function calculateItemTotal(Set $set, Get $get): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $price = floatval($get('purchase_price') ?? 0);
        $discount = floatval($get('discount') ?? 0);

        $subtotal = $quantity * $price;
        $total = $subtotal - $discount;

        $set('subtotal', $subtotal);
        $set('total', max(0, $total));
    }

    private static function calculateTotals(Set $set, Get $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += floatval($item['total'] ?? 0);
        }

        $set('subtotal', $subtotal);
        self::calculateGrandTotal($set, $get);
    }

    private static function calculateGrandTotal(Set $set, Get $get): void
    {
        $subtotal = floatval($get('subtotal') ?? 0);
        $discount = floatval($get('discount') ?? 0);
        $tax = floatval($get('tax') ?? 0);

        $total = $subtotal - $discount + $tax;
        $set('total', max(0, $total));
    }
}
