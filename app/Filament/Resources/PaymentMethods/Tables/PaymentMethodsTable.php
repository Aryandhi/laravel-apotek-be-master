<?php

namespace App\Filament\Resources\PaymentMethods\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentMethodsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Metode')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_cash')
                    ->label('Tunai')
                    ->boolean()
                    ->trueIcon('heroicon-o-banknotes')
                    ->falseIcon('heroicon-o-credit-card')
                    ->trueColor('success')
                    ->falseColor('info')
                    ->alignCenter(),
                TextColumn::make('account_number')
                    ->label('No. Rekening')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('account_name')
                    ->label('Atas Nama')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('sale_payments_count')
                    ->label('Transaksi')
                    ->counts('salePayments')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->alignCenter(),
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
            ->defaultSort('name')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
                TernaryFilter::make('is_cash')
                    ->label('Tipe')
                    ->placeholder('Semua')
                    ->trueLabel('Tunai')
                    ->falseLabel('Non-Tunai'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('payment-methods.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('payment-methods.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('payment-methods.delete')),
                ]),
            ]);
    }
}
