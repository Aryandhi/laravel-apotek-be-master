<?php

namespace App\Filament\Resources\StockOpnames\Schemas;

use App\Enums\StockOpnameStatus;
use App\Models\Product;
use App\Models\ProductBatch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StockOpnameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kode Opname')
                    ->required()
                    ->default(fn () => 'SO-'.date('Ymd').'-'.str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT))
                    ->unique(ignoreRecord: true),

                DatePicker::make('date')
                    ->label('Tanggal')
                    ->required()
                    ->default(now()),

                Select::make('status')
                    ->label('Status')
                    ->options(StockOpnameStatus::class)
                    ->default(StockOpnameStatus::Draft)
                    ->disabled()
                    ->dehydrated(),

                Hidden::make('user_id')
                    ->default(fn () => auth()->id()),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->relationship()
                    ->label('Item Stock Opname')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(Product::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('product_batch_id', null);
                                $set('system_stock', 0);
                                $set('physical_stock', 0);
                                $set('difference', 0);
                            }),

                        Select::make('product_batch_id')
                            ->label('Batch')
                            ->options(function (callable $get) {
                                $productId = $get('product_id');
                                if (! $productId) {
                                    return [];
                                }

                                return ProductBatch::where('product_id', $productId)
                                    ->get()
                                    ->mapWithKeys(fn ($batch) => [
                                        $batch->id => "{$batch->batch_number} (Exp: {$batch->expired_date->format('d/m/Y')}) - Stok: {$batch->stock}",
                                    ]);
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $batchId = $get('product_batch_id');
                                if ($batchId) {
                                    $batch = ProductBatch::find($batchId);
                                    if ($batch) {
                                        $set('system_stock', $batch->stock);
                                        $set('physical_stock', $batch->stock);
                                        $set('difference', 0);
                                    }
                                }
                            }),

                        TextInput::make('system_stock')
                            ->label('Stok Sistem')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),

                        TextInput::make('physical_stock')
                            ->label('Stok Fisik')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $physical = (int) $get('physical_stock');
                                $system = (int) $get('system_stock');
                                $set('difference', $physical - $system);
                            }),

                        TextInput::make('difference')
                            ->label('Selisih')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->default(0),

                        TextInput::make('notes')
                            ->label('Keterangan')
                            ->placeholder('Alasan selisih (opsional)'),
                    ])
                    ->columns(6)
                    ->defaultItems(0)
                    ->addActionLabel('Tambah Item')
                    ->reorderable(false)
                    ->columnSpanFull()
                    ->itemLabel(fn (array $state): ?string => $state['product_id']
                        ? Product::find($state['product_id'])?->name.' - Selisih: '.($state['difference'] ?? 0)
                        : 'Item Baru'
                    ),
            ]);
    }
}
