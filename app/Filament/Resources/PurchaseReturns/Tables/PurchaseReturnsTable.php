<?php

namespace App\Filament\Resources\PurchaseReturns\Tables;

use App\Enums\PurchaseReturnStatus;
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

class PurchaseReturnsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Retur')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchase.invoice_number')
                    ->label('No. Pembelian')
                    ->placeholder('-')
                    ->searchable()
                    ->url(fn ($record) => $record->purchase_id
                        ? route('filament.admin.resources.purchases.edit', $record->purchase_id)
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Item')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->suffix(' produk'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PurchaseReturnStatus $state) => $state->color())
                    ->formatStateUsing(fn (PurchaseReturnStatus $state) => $state->label()),
                TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PurchaseReturnStatus::class)
                    ->multiple(),
                SelectFilter::make('supplier')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('has_purchase')
                    ->label('Dengan Ref. Pembelian')
                    ->query(fn (Builder $query) => $query->whereNotNull('purchase_id')),
                Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query) => $query->whereMonth('date', now()->month)->whereYear('date', now()->year)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('purchase-returns.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('purchase-returns.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('purchase-returns.delete')),
                ]),
            ]);
    }
}
