<?php

namespace App\Filament\Resources\SaleReturns\Tables;

use App\Enums\RefundMethod;
use App\Enums\SaleReturnStatus;
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

class SaleReturnsTable
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
                TextColumn::make('sale.invoice_number')
                    ->label('No. Penjualan')
                    ->searchable()
                    ->url(fn ($record) => $record->sale_id
                        ? route('filament.admin.resources.sales.edit', $record->sale_id)
                        : null)
                    ->openUrlInNewTab(),
                TextColumn::make('customer.name')
                    ->label('Pelanggan')
                    ->placeholder('Umum')
                    ->searchable()
                    ->toggleable(),
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
                TextColumn::make('refund_method')
                    ->label('Metode Refund')
                    ->badge()
                    ->color(fn (?RefundMethod $state) => $state?->color() ?? 'gray')
                    ->formatStateUsing(fn (?RefundMethod $state) => $state?->label() ?? '-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (SaleReturnStatus $state) => $state->color())
                    ->formatStateUsing(fn (SaleReturnStatus $state) => $state->label()),
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
                    ->options(SaleReturnStatus::class)
                    ->multiple(),
                SelectFilter::make('refund_method')
                    ->label('Metode Refund')
                    ->options(RefundMethod::class),
                SelectFilter::make('customer')
                    ->label('Pelanggan')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('today')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query) => $query->whereDate('date', today())),
                Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query) => $query->whereMonth('date', now()->month)->whereYear('date', now()->year)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('sale-returns.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('sale-returns.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('sale-returns.delete')),
                ]),
            ]);
    }
}
