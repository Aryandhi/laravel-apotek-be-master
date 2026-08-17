<?php

namespace App\Filament\Resources\ProductBatches\Schemas;

use App\Enums\BatchStatus;
use App\Models\Purchase;
use App\Services\BatchPricingSyncService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                                    ->maxLength(100)
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Nomor batch ini sudah digunakan oleh produk lain. Gunakan nomor batch yang berbeda.',
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('expired_date')
                                    ->label('Tanggal Kadaluarsa')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d M Y'),
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
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncPricing($set, $get, (bool) $get('is_manual_selling_price')))
                                    ->prefix('Rp')
                                    ->minValue(0),
                                TextInput::make('margin_percentage')
                                    ->label('Margin %')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, Get $get): void {
                                        $set('is_manual_selling_price', false);
                                        self::syncPricing($set, $get, false);
                                    })
                                    ->suffix('%')
                                    ->minValue(0),
                            ]),
                        Grid::make(1)
                            ->schema([
                                Hidden::make('is_manual_selling_price')
                                    ->default(false)
                                    ->dehydrated(false),
                                TextInput::make('selling_price')
                                    ->label('Harga Jual')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(fn (Get $get): bool => ! (bool) $get('is_manual_selling_price'))
                                    ->dehydrated()
                                    ->prefix('Rp')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, Get $get) => self::syncPricing($set, $get, true))
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
                                                    self::syncPricing($set, $get, false);
                                                }
                                            })
                                    )
                                    ->minValue(0)
                                    ->helperText('Harga jual dihitung otomatis dari harga beli dan margin'),
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

    private static function syncPricing(Set $set, Get $get, bool $preferSellingPrice = false): void
    {
        $purchasePrice = floatval($get('purchase_price') ?? 0);
        $margin = floatval($get('margin_percentage') ?? 0);
        $sellingPrice = floatval($get('selling_price') ?? 0);

        if ($preferSellingPrice) {
            $margin = app(BatchPricingSyncService::class)->calculateMarginPercentage($purchasePrice, $sellingPrice);
        } else {
            $sellingPrice = app(BatchPricingSyncService::class)->calculateSellingPrice($purchasePrice, $margin);
        }

        $set('margin_percentage', round($margin, 2));
        $set('selling_price', round($sellingPrice, 2));
    }
}
