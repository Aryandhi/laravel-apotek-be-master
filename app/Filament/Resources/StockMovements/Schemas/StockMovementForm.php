<?php

namespace App\Filament\Resources\StockMovements\Schemas;

use App\Enums\StockMovementType;
use App\Models\ProductBatch;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockMovementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Mutasi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('product_batch_id')
                                    ->label('Batch Produk')
                                    ->relationship('productBatch', 'batch_number')
                                    ->getOptionLabelFromRecordUsing(fn (ProductBatch $record) => "{$record->product->name} - {$record->batch_number} (Stok: {$record->stock})")
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $batch = ProductBatch::find($state);
                                            if ($batch) {
                                                $set('stock_before', $batch->stock);
                                            }
                                        }
                                    }),
                                Select::make('type')
                                    ->label('Tipe Mutasi')
                                    ->options(StockMovementType::class)
                                    ->required()
                                    ->native(false),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->required()
                                    ->numeric()
                                    ->minValue(1)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $stockBefore = $get('stock_before') ?? 0;
                                        $type = $get('type');
                                        $qty = (int) $state;

                                        if ($type) {
                                            $movementType = $type instanceof StockMovementType
                                                ? $type
                                                : StockMovementType::tryFrom($type);

                                            if ($movementType?->isIncoming()) {
                                                $set('stock_after', $stockBefore + $qty);
                                            } else {
                                                $set('stock_after', max(0, $stockBefore - $qty));
                                            }
                                        }
                                    }),
                                TextInput::make('stock_before')
                                    ->label('Stok Sebelum')
                                    ->required()
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('stock_after')
                                    ->label('Stok Sesudah')
                                    ->required()
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
                            ]),
                    ]),

                Section::make('Referensi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('reference_type')
                                    ->label('Tipe Referensi')
                                    ->options([
                                        'App\\Models\\Purchase' => 'Pembelian',
                                        'App\\Models\\Sale' => 'Penjualan',
                                        'App\\Models\\PurchaseReturn' => 'Retur Pembelian',
                                        'App\\Models\\SaleReturn' => 'Retur Penjualan',
                                        'App\\Models\\StockOpname' => 'Stock Opname',
                                    ])
                                    ->native(false)
                                    ->placeholder('Pilih tipe referensi (opsional)'),
                                TextInput::make('reference_id')
                                    ->label('ID Referensi')
                                    ->numeric()
                                    ->placeholder('ID dokumen referensi'),
                            ]),
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->default(fn () => auth()->id())
                            ->disabled()
                            ->dehydrated(),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan untuk mutasi ini...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
