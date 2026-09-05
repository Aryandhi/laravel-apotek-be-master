<?php

namespace App\Filament\Resources\Purchases\Schemas;

use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseStatus;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Services\BatchPricingSyncService;
use Filament\Actions\Action;
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
use Illuminate\Support\Facades\Auth;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembelian')
                    ->description('Data dasar pembelian')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('No. Invoice')
                                    ->required()
                                    ->placeholder('cth: INV-2026-001')
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Nomor invoice ini sudah digunakan. Gunakan nomor invoice yang berbeda.',
                                    ]),
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
                        Select::make('purchase_order_id')
                            ->label('No. Surat Pesanan')
                            ->options(fn () => PurchaseOrder::query()
                                ->whereIn('status', [PurchaseOrderStatus::Order, PurchaseOrderStatus::Partial])
                                ->orderByDesc('order_date')
                                ->get()
                                ->mapWithKeys(fn (PurchaseOrder $order) => [
                                    $order->id => "{$order->po_number} - {$order->title}",
                                ])
                                ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (?Purchase $record) => filled($record))
                            ->helperText('Pilih untuk mengisi otomatis item dari Surat Pesanan (hanya item yang belum diinput di faktur lain yang ditampilkan)')
                            ->afterStateUpdated(function (Set $set, Get $get, ?int $state) {
                                if (! $state) {
                                    return;
                                }

                                self::fillItemsFromPurchaseOrder($set, $get, $state);
                            }),
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
                                                if (! $state) {
                                                    $set('unit_id', null);
                                                    $set('purchase_price', null);
                                                    $set('margin_percentage', null);
                                                    $set('selling_price', null);
                                                    $set('subtotal', 0);
                                                    $set('total', 0);
                                                    $set('is_manual_selling_price', false);

                                                    return;
                                                }

                                                if ($state) {
                                                    $product = Product::find($state);
                                                    if ($product) {
                                                        $batch = self::resolveExistingBatch($state, $get('batch_number'));

                                                        if ($batch) {
                                                            $set('purchase_price', self::toFloat($batch->purchase_price));
                                                            $set('margin_percentage', self::toFloat($batch->margin_percentage));
                                                            $set('selling_price', self::toFloat($batch->selling_price));
                                                        } else {
                                                            $productPurchasePrice = self::toFloat($product->purchase_price);
                                                            $productSellingPrice = self::toFloat($product->selling_price);
                                                            $derivedMargin = self::calculateMarginPercentage($productPurchasePrice, $productSellingPrice);

                                                            $set('purchase_price', $productPurchasePrice);
                                                            $set('margin_percentage', $derivedMargin);
                                                            $set('selling_price', $productSellingPrice > 0
                                                                ? $productSellingPrice
                                                                : self::calculateSellingPrice($productPurchasePrice, $derivedMargin)
                                                            );
                                                        }

                                                        $set('is_manual_selling_price', false);
                                                        $set('unit_id', $product->base_unit_id);
                                                        self::calculateItemTotal($set, $get, false);
                                                    }
                                                }
                                            })
                                            ->columnSpan(2),
                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->required()
                                            ->default(null)
                                            ->minValue(fn (Get $get): int => filled($get('purchase_order_item_id')) ? 0 : 1)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get))
                                            ->columnSpan(1),
                                        Select::make('unit_id')
                                            ->label('Satuan')
                                            ->relationship('unit', 'name')
                                            ->required()
                                            ->preload()
                                            ->disabled()
                                            ->dehydrated()
                                            ->columnSpan(1),
                                        TextInput::make('purchase_price')
                                            ->label('Harga Beli')
                                            ->numeric()
                                            ->required()
                                            ->prefix('Rp')
                                            ->default(null)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get, (bool) $get('is_manual_selling_price')))
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
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get, ?string $state): void {
                                                $productId = $get('product_id');
                                                if (! $productId || ! filled($state)) {
                                                    return;
                                                }

                                                $batch = self::resolveExistingBatch((int) $productId, $state);
                                                if (! $batch) {
                                                    return;
                                                }

                                                $set('purchase_price', self::toFloat($batch->purchase_price));
                                                $set('margin_percentage', self::toFloat($batch->margin_percentage));
                                                $set('selling_price', self::toFloat($batch->selling_price));
                                                $set('is_manual_selling_price', false);

                                                self::calculateItemTotal($set, $get, false);
                                            })
                                            ->maxLength(100),
                                        DatePicker::make('expired_date')
                                            ->label('Tgl Kadaluarsa')
                                            ->native(false)
                                            ->displayFormat('d M Y')
                                            ->minDate(now()),
                                        TextInput::make('margin_percentage')
                                            ->label('Margin %')
                                            ->numeric()
                                            ->required()
                                            ->default(null)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function (Set $set, Get $get): void {
                                                $set('is_manual_selling_price', false);
                                                self::calculateItemTotal($set, $get, false);
                                            })
                                            ->suffix('%')
                                            ->minValue(0),
                                        TextInput::make('selling_price')
                                            ->label('Harga Jual')
                                            ->numeric()
                                            ->default(0)
                                            ->disabled(fn (Get $get): bool => ! (bool) $get('is_manual_selling_price'))
                                            ->dehydrated()
                                            ->prefix('Rp')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get, true))
                                            ->suffixAction(
                                                Action::make('toggleManualSellingPrice')
                                                    ->icon(fn (Get $get): string => (bool) $get('is_manual_selling_price') ? 'heroicon-o-lock-open' : 'heroicon-o-pencil-square')
                                                    ->tooltip(fn (Get $get): string => (bool) $get('is_manual_selling_price')
                                                        ? 'Kunci kembali harga jual otomatis'
                                                        : 'Edit manual harga jual')
                                                    ->action(function (Set $set, Get $get): void {
                                                        $isManualSellingPrice = (bool) $get('is_manual_selling_price');
                                                        $set('is_manual_selling_price', ! $isManualSellingPrice);

                                                        if ($isManualSellingPrice) {
                                                            self::calculateItemTotal($set, $get, false);
                                                        }
                                                    })
                                            )
                                            ->helperText('Harga jual dihitung otomatis dari harga beli dan margin'),
                                        TextInput::make('discount')
                                            ->label('Diskon')
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, Get $get) => self::calculateItemTotal($set, $get)),
                                    ]),
                                Hidden::make('subtotal')
                                    ->default(0),
                                Hidden::make('received_quantity')
                                    ->default(0),
                                Hidden::make('purchase_order_item_id')
                                    ->default(null),
                                Hidden::make('is_manual_selling_price')
                                    ->default(false)
                                    ->dehydrated(false),
                            ])
                            ->columns(1)
                            ->addActionLabel('Tambah Barang')
                            ->reorderable(false)
                            ->collapsible()
                            ->collapseAllAction(fn ($action) => $action->label('Sembunyikan Detail'))
                            ->expandAllAction(fn ($action) => $action->label('Tampilkan Detail'))
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
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Total diskon otomatis dari item barang'),
                                TextInput::make('tax')
                                    ->label('Pajak')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->live(onBlur: true)
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
                                    ->live(onBlur: true),
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
                            ->default(fn () => Auth::id())
                            ->dehydrated(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    private static function calculateItemTotal(Set $set, Get $get, bool $preferSellingPrice = false): void
    {
        $quantity = self::toFloat($get('quantity') ?? 0);
        $price = self::toFloat($get('purchase_price') ?? 0);
        $margin = self::toFloat($get('margin_percentage') ?? 0);
        $sellingPrice = self::toFloat($get('selling_price') ?? 0);
        $discount = self::toFloat($get('discount') ?? 0);

        if ($preferSellingPrice) {
            $margin = self::calculateMarginPercentage($price, $sellingPrice);
        } else {
            $sellingPrice = self::calculateSellingPrice($price, $margin);
        }

        $subtotal = $quantity * $price;
        $total = max(0, $subtotal - $discount);

        $set('selling_price', round($sellingPrice));
        $set('margin_percentage', round($margin, 2));
        $set('subtotal', round($subtotal, 2));
        $set('total', round($total, 2));
    }

    private static function calculateSellingPrice(float $purchasePrice, float $marginPercentage): float
    {
        return app(BatchPricingSyncService::class)->calculateSellingPrice($purchasePrice, $marginPercentage);
    }

    private static function calculateMarginPercentage(float $purchasePrice, float $sellingPrice): float
    {
        return app(BatchPricingSyncService::class)->calculateMarginPercentage($purchasePrice, $sellingPrice);
    }

    private static function resolveExistingBatch(int $productId, ?string $batchNumber = null): ?ProductBatch
    {
        $service = app(BatchPricingSyncService::class);

        $batch = $service->findBatchByProductAndNumber($productId, $batchNumber);
        if ($batch) {
            return $batch;
        }

        return $service->findLatestBatchByProduct($productId);
    }

    /**
     * Populate the items repeater and supplier from the selected Surat Pesanan (Purchase Order).
     * Only items that still have a remaining (backorder) quantity are included, and quantity
     * defaults to 0 so the admin must confirm what was actually received (0 = not received).
     */
    private static function fillItemsFromPurchaseOrder(Set $set, Get $get, int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::query()
            ->with(['items.product', 'items.unit'])
            ->find($purchaseOrderId);

        if (! $purchaseOrder) {
            return;
        }

        $set('supplier_id', $purchaseOrder->supplier_id);

        $rows = [];

        foreach ($purchaseOrder->items as $orderItem) {
            $remaining = $orderItem->remaining_quantity;

            if ($remaining <= 0) {
                continue;
            }

            $product = $orderItem->product;
            if (! $product) {
                continue;
            }

            $batch = self::resolveExistingBatch($product->id);
            $purchasePrice = $batch ? self::toFloat($batch->purchase_price) : self::toFloat($product->purchase_price);
            $margin = $batch ? self::toFloat($batch->margin_percentage) : self::calculateMarginPercentage($purchasePrice, self::toFloat($product->selling_price));
            $sellingPrice = $batch ? self::toFloat($batch->selling_price) : self::calculateSellingPrice($purchasePrice, $margin);

            $rows[] = [
                'product_id' => $product->id,
                'purchase_order_item_id' => $orderItem->id,
                'quantity' => 0,
                'unit_id' => $orderItem->unit_id,
                'purchase_price' => $purchasePrice,
                'margin_percentage' => round($margin, 2),
                'selling_price' => round($sellingPrice),
                'discount' => 0,
                'subtotal' => 0,
                'total' => 0,
                'received_quantity' => 0,
                'is_manual_selling_price' => false,
            ];
        }

        $set('items', $rows);
        self::calculateTotals($set, $get);
    }

    private static function calculateTotals(Set $set, Get $get): void
    {
        $items = $get('items') ?? [];
        $subtotal = 0.0;
        $discount = 0.0;

        foreach ($items as $item) {
            $subtotal += self::toFloat($item['subtotal'] ?? 0);
            $discount += self::toFloat($item['discount'] ?? 0);
        }

        $set('subtotal', round($subtotal, 2));
        $set('discount', round($discount, 2));
        self::calculateGrandTotal($set, $get);
    }

    private static function calculateGrandTotal(Set $set, Get $get): void
    {
        $subtotal = self::toFloat($get('subtotal') ?? 0);
        $discount = self::toFloat($get('discount') ?? 0);
        $tax = self::toFloat($get('tax') ?? 0);

        $total = $subtotal - $discount + $tax;
        $set('total', round(max(0, $total), 2));
    }

    private static function toFloat(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return 0.0;
    }
}
