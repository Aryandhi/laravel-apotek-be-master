<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('store.name')
                    ->label('Toko')
                    ->placeholder('Global')
                    ->badge()
                    ->color(fn ($state) => $state ? 'info' : 'gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('key')
                    ->label('Kunci')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kunci disalin!'),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->limit(50)
                    ->placeholder('-')
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
            ->filters([
                SelectFilter::make('store_id')
                    ->label('Toko')
                    ->relationship('store', 'name')
                    ->placeholder('Semua Toko'),
                SelectFilter::make('group')
                    ->label('Grup')
                    ->options(fn () => \App\Models\Setting::query()
                        ->distinct()
                        ->pluck('group', 'group')
                        ->toArray()),
                TernaryFilter::make('is_global')
                    ->label('Pengaturan Global')
                    ->placeholder('Semua')
                    ->trueLabel('Global Saja')
                    ->falseLabel('Per Toko Saja')
                    ->queries(
                        true: fn ($query) => $query->whereNull('store_id'),
                        false: fn ($query) => $query->whereNotNull('store_id'),
                    ),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('settings.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('settings.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('settings.delete')),
                ]),
            ]);
    }
}
