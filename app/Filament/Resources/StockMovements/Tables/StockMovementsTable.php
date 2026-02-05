<?php

namespace App\Filament\Resources\StockMovements\Tables;

use App\Enums\StockMovementType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('productBatch.product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('productBatch.batch_number')
                    ->label('No. Batch')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (StockMovementType $state): string => $state->color())
                    ->formatStateUsing(fn (StockMovementType $state): string => $state->label()),
                TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(function ($state, $record) {
                        $prefix = $record->type->isIncoming() ? '+' : '-';

                        return $prefix.$state;
                    })
                    ->color(fn ($record) => $record->type->isIncoming() ? 'success' : 'danger'),
                TextColumn::make('stock_before')
                    ->label('Stok Sebelum')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('stock_after')
                    ->label('Stok Sesudah')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('reference_display')
                    ->label('Referensi')
                    ->state(function ($record) {
                        if (! $record->reference_type || ! $record->reference_id) {
                            return '-';
                        }

                        $type = match ($record->reference_type) {
                            'App\\Models\\Purchase' => 'Pembelian',
                            'App\\Models\\Sale' => 'Penjualan',
                            'App\\Models\\PurchaseReturn' => 'Retur Beli',
                            'App\\Models\\SaleReturn' => 'Retur Jual',
                            'App\\Models\\StockOpname' => 'Stock Opname',
                            default => class_basename($record->reference_type),
                        };

                        return "{$type} #{$record->reference_id}";
                    }),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe Mutasi')
                    ->options(StockMovementType::class)
                    ->multiple(),
                SelectFilter::make('product')
                    ->label('Produk')
                    ->relationship('productBatch.product', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('stock.delete')),
                ]),
            ]);
    }
}
