<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('categoryType.name')
                    ->label('Tipe Kategori')
                    ->badge()
                    ->color(fn ($record) => $record->categoryType?->color ?? 'gray')
                    ->placeholder('Belum diset'),
                TextColumn::make('type')
                    ->label('Tipe (Legacy)')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('requires_prescription')
                    ->label('Resep')
                    ->boolean(),
                IconColumn::make('is_narcotic')
                    ->label('Narkotika')
                    ->boolean(),
                TextColumn::make('products_count')
                    ->label('Produk')
                    ->counts('products')
                    ->badge()
                    ->color('info'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_type_id')
                    ->label('Tipe Kategori')
                    ->relationship('categoryType', 'name'),
                TernaryFilter::make('requires_prescription')
                    ->label('Memerlukan Resep'),
                TernaryFilter::make('is_narcotic')
                    ->label('Narkotika'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('categories.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('categories.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('categories.delete')),
                ]),
            ]);
    }
}
