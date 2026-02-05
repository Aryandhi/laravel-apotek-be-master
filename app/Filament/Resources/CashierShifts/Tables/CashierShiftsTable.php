<?php

namespace App\Filament\Resources\CashierShifts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CashierShiftsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('opening_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('closing_time')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('opening_cash')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('expected_cash')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('actual_cash')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('difference')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('cashier-shifts.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('cashier-shifts.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('cashier-shifts.delete')),
                ]),
            ]);
    }
}
