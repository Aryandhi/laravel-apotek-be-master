<?php

namespace App\Filament\Resources\ProductBatches\Tables;

use App\Enums\BatchStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductBatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch_number')
                    ->label('No. Batch')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('expired_date')
                    ->label('Kadaluarsa')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => match (true) {
                        $record->expired_date->isPast() => 'danger',
                        $record->expired_date->diffInDays(now()) <= 90 => 'warning',
                        default => 'success',
                    })
                    ->description(fn ($record) => match (true) {
                        $record->expired_date->isPast() => 'Sudah kadaluarsa',
                        $record->expired_date->diffInDays(now()) <= 30 => $record->expired_date->diffInDays(now()).' hari lagi',
                        $record->expired_date->diffInDays(now()) <= 90 => 'Mendekati kadaluarsa',
                        default => null,
                    }),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->numeric()
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'success' : 'danger')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('selling_price')
                    ->label('Harga Jual')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (BatchStatus $state) => $state->color())
                    ->formatStateUsing(fn (BatchStatus $state) => $state->label()),
                TextColumn::make('initial_stock')
                    ->label('Stok Awal')
                    ->numeric()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stock_movements_count')
                    ->label('Mutasi')
                    ->counts('stockMovements')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diubah')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('expired_date', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(BatchStatus::class),
                SelectFilter::make('product')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('supplier')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('expired')
                    ->label('Sudah Kadaluarsa')
                    ->query(fn (Builder $query) => $query->where('expired_date', '<', now())),
                Filter::make('near_expired')
                    ->label('Mendekati Kadaluarsa (90 hari)')
                    ->query(fn (Builder $query) => $query->whereBetween('expired_date', [now(), now()->addDays(90)])),
                Filter::make('has_stock')
                    ->label('Ada Stok')
                    ->query(fn (Builder $query) => $query->where('stock', '>', 0)),
                Filter::make('out_of_stock')
                    ->label('Stok Habis')
                    ->query(fn (Builder $query) => $query->where('stock', '<=', 0)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('stock.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('stock.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('stock.delete')),
                ]),
            ]);
    }
}
