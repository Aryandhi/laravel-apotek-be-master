<?php

namespace App\Filament\Resources\StockOpnames\Schemas;

use App\Enums\StockOpnameStatus;
use App\Filament\Resources\StockOpnames\Pages\CreateStockOpname;
use App\Models\Product;
use App\Models\ProductBatch;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                    ->default(fn () => Auth::id()),

                Textarea::make('notes')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),

                Section::make('Filter & Progres')
                    ->description('Gunakan filter untuk melihat item tertentu berdasarkan produk atau lokasi rak. Saat item baru ditambahkan, daftar pilihan akan otomatis diperbarui.')
                    ->schema([
                        Grid::make()
                            ->schema([
                                Select::make('product_filter')
                                    ->label('Filter Nama Produk')
                                    ->options(function (callable $get) {
                                        $items = $get('items_source') ?? $get('items') ?? [];

                                        return ['' => 'Semua Produk Terinput'] + CreateStockOpname::getAvailableProductOptionsForItems($items);
                                    })
                                    ->searchable()
                                    ->live()
                                    ->dehydrated(false)
                                    ->placeholder('Semua Produk Terinput')
                                    ->helperText('Pilih produk dari item stock opname yang sudah ada.')
                                    ->default('')
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $items = $get('items_source') ?? $get('items') ?? [];
                                        $filteredItems = CreateStockOpname::getFilteredItemsForStockOpname($items, (int) $state, $get('rack_filter'));
                                        $set('items', $filteredItems);
                                    }),

                                Select::make('rack_filter')
                                    ->label('Filter Lokasi Rak')
                                    ->options(function (callable $get) {
                                        $items = $get('items_source') ?? $get('items') ?? [];

                                        return ['' => 'Semua Lokasi Rak Terinput'] + CreateStockOpname::getAvailableRackLocationOptionsForItems($items);
                                    })
                                    ->searchable()
                                    ->live()
                                    ->dehydrated(false)
                                    ->placeholder('Semua Lokasi Rak Terinput')
                                    ->helperText('Pilih lokasi rak yang terkait dengan item stock opname.')
                                    ->default('')
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $items = $get('items_source') ?? $get('items') ?? [];
                                        $filteredItems = CreateStockOpname::getFilteredItemsForStockOpname($items, (int) $get('product_filter'), $state);
                                        $set('items', $filteredItems);
                                    }),
                            ])
                            ->columns(2),

                        Hidden::make('items_source')
                            ->dehydrated(false)
                            ->default([])
                            ->afterStateHydrated(function ($state, callable $set, callable $get) {
                                $items = $get('items') ?? [];
                                $set('items_source', $items);
                            }),
                    ])
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->relationship()
                    ->label('Item Stock Opname')
                    ->afterStateHydrated(function ($state, callable $set) {
                        $set('items_source', $state ?? []);
                    })
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $productFilter = (int) ($get('product_filter') ?? 0);
                        $rackFilter = (string) ($get('rack_filter') ?? '');
                        $previousSource = $get('items_source') ?? [];

                        // Merge the edited/added/removed visible items back into the full
                        // source so items hidden by the active filter aren't lost.
                        $mergedSource = CreateStockOpname::mergeVisibleItemsIntoSource($previousSource, $state ?? [], $productFilter, $rackFilter);
                        $set('items_source', $mergedSource);

                        $filteredItems = CreateStockOpname::getFilteredItemsForStockOpname($mergedSource, $productFilter, $rackFilter);
                        $set('items', $filteredItems);
                    })
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->options(Product::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
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
