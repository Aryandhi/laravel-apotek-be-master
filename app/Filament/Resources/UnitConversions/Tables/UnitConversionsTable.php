<?php

namespace App\Filament\Resources\UnitConversions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UnitConversionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fromUnit.name')
                    ->label('Dari Satuan')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('conversion_value')
                    ->label('Nilai')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->formatStateUsing(fn ($state) => '× '.number_format($state, 2)),
                TextColumn::make('toUnit.name')
                    ->label('Ke Satuan')
                    ->badge()
                    ->color('success')
                    ->searchable(),
                TextColumn::make('conversion_display')
                    ->label('Keterangan')
                    ->state(function ($record) {
                        return "1 {$record->fromUnit->name} = {$record->conversion_value} {$record->toUnit->name}";
                    })
                    ->color('gray')
                    ->toggleable(),
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
            ->defaultSort('product.name')
            ->filters([
                SelectFilter::make('product')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('from_unit')
                    ->label('Dari Satuan')
                    ->relationship('fromUnit', 'name')
                    ->preload(),
                SelectFilter::make('to_unit')
                    ->label('Ke Satuan')
                    ->relationship('toUnit', 'name')
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('units.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('units.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('units.delete')),
                ]),
            ]);
    }
}
