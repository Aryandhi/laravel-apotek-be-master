<?php

namespace App\Filament\Resources\SaleReturns\Schemas;

use App\Enums\RefundMethod;
use App\Enums\SaleReturnStatus;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class SaleReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Retur')
                    ->description('Data dasar retur penjualan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Kode Retur')
                                    ->required()
                                    ->placeholder('cth: SRT-2026-001')
                                    ->maxLength(100),
                                DatePicker::make('date')
                                    ->label('Tanggal')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->default(now()),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(SaleReturnStatus::class)
                                    ->default('pending')
                                    ->required(),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Select::make('sale_id')
                                    ->label('Referensi Penjualan')
                                    ->options(fn () => Sale::query()
                                        ->where('status', 'completed')
                                        ->latest()
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(fn ($sale) => [
                                            $sale->id => "{$sale->invoice_number} - {$sale->date->format('d M Y')} (Rp ".number_format($sale->total, 0, ',', '.').')',
                                        ])
                                        ->toArray()
                                    )
                                    ->searchable()
                                    ->required()
                                    ->placeholder('Pilih penjualan')
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, ?int $state) {
                                        if ($state) {
                                            $sale = Sale::find($state);
                                            if ($sale) {
                                                $set('customer_id', $sale->customer_id);
                                            }
                                        }
                                    }),
                                Select::make('customer_id')
                                    ->label('Pelanggan')
                                    ->relationship('customer', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pelanggan umum'),
                            ]),
                    ]),

                Section::make('Daftar Barang Retur')
                    ->description('Tambahkan produk yang akan diretur')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Grid::make(5)
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
                                                        $set('price', $product->selling_price);
                                                        $set('product_batch_id', null);
                                                        self::calculateItemSubtotal($set, $get);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                        Select::make('product_batch_id')
                                            ->label('Batch')
                                            ->options(function (Get $get) {
                                                $productId = $get('product_id');
                                                if (! $productId) {
                                                    return [];
                                                }

                                                return ProductBatch::query()
                                                    ->where('product_id', $productId)
                                                    ->get()
                                                    ->mapWithKeys(fn ($batch) => [
                                                        $batch->id => "{$batch->batch_number} (Stok: {$batch->stock})",
                                                    ])
                                                    ->toArray();
                                            })
                                            ->searchable()
                                            ->placeholder('Pilih batch')
                                            ->helperText('Opsional'),
                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(1)
                                            ->minValue(1)
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemSubtotal($set, $get)),
                                        TextInput::make('price')
                                            ->label('Harga')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->reactive()
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemSubtotal($set, $get)),
                                    ]),
                                Grid::make(2)
                                    ->schema([
                                        Textarea::make('reason')
                                            ->label('Alasan Retur Item')
                                            ->placeholder('Alasan retur untuk item ini...')
                                            ->rows(2),
                                        TextInput::make('subtotal')
                                            ->label('Subtotal')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->disabled()
                                            ->dehydrated()
                                            ->default(0),
                                    ]),
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
                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateTotal($set, $get))
                            ->deleteAction(
                                fn ($action) => $action->after(fn (Set $set, Get $get) => self::calculateTotal($set, $get))
                            ),
                    ]),

                Section::make('Detail Retur')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Textarea::make('reason')
                                    ->label('Alasan Retur Umum')
                                    ->placeholder('Jelaskan alasan retur secara umum...')
                                    ->rows(3),
                                Select::make('refund_method')
                                    ->label('Metode Refund')
                                    ->options(RefundMethod::class)
                                    ->required()
                                    ->default('cash')
                                    ->helperText('Pilih cara pengembalian dana'),
                                TextInput::make('total')
                                    ->label('Total Nilai Retur')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->helperText('Dihitung otomatis dari item'),
                            ]),
                    ]),

                Section::make('Informasi Tambahan')
                    ->schema([
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

    private static function calculateItemSubtotal(Set $set, Get $get): void
    {
        $quantity = floatval($get('quantity') ?? 0);
        $price = floatval($get('price') ?? 0);

        $subtotal = $quantity * $price;
        $set('subtotal', $subtotal);
    }

    private static function calculateTotal(Set $set, Get $get): void
    {
        $items = $get('items') ?? [];
        $total = 0;

        foreach ($items as $item) {
            $total += floatval($item['subtotal'] ?? 0);
        }

        $set('total', $total);
    }
}
