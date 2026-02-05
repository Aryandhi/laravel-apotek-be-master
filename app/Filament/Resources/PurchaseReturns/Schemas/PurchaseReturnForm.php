<?php

namespace App\Filament\Resources\PurchaseReturns\Schemas;

use App\Enums\PurchaseReturnStatus;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
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

class PurchaseReturnForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Retur')
                    ->description('Data dasar retur pembelian')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Kode Retur')
                                    ->required()
                                    ->placeholder('cth: RTR-2026-001')
                                    ->maxLength(100),
                                DatePicker::make('date')
                                    ->label('Tanggal')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->default(now()),
                                Select::make('status')
                                    ->label('Status')
                                    ->options(PurchaseReturnStatus::class)
                                    ->default('pending')
                                    ->required(),
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
                                Select::make('purchase_id')
                                    ->label('Referensi Pembelian')
                                    ->options(fn () => Purchase::query()
                                        ->latest()
                                        ->limit(100)
                                        ->get()
                                        ->mapWithKeys(fn ($purchase) => [
                                            $purchase->id => "{$purchase->invoice_number} - {$purchase->date->format('d M Y')} ({$purchase->supplier?->name})",
                                        ])
                                        ->toArray()
                                    )
                                    ->searchable()
                                    ->placeholder('Pilih pembelian (opsional)')
                                    ->helperText('Pilih jika retur terkait pembelian tertentu'),
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
                                                        $set('price', $product->purchase_price);
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
                                                    ->where('stock', '>', 0)
                                                    ->get()
                                                    ->mapWithKeys(fn ($batch) => [
                                                        $batch->id => "{$batch->batch_number} (Stok: {$batch->stock}, Exp: {$batch->expired_date->format('d M Y')})",
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
                        Grid::make(2)
                            ->schema([
                                Textarea::make('reason')
                                    ->label('Alasan Retur Umum')
                                    ->placeholder('Jelaskan alasan retur barang secara umum...')
                                    ->rows(3),
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
