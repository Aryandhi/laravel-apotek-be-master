<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->placeholder('System'),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'created' => 'Dibuat',
                        'updated' => 'Diubah',
                        'deleted' => 'Dihapus',
                        default => $state,
                    }),
                TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->badge()
                    ->color('info'),
                TextColumn::make('model_id')
                    ->label('ID')
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->getStateUsing(function ($record) {
                        $model = $record->model_type ? class_basename($record->model_type) : 'Data';
                        $action = match ($record->action) {
                            'created' => 'membuat',
                            'updated' => 'mengubah',
                            'deleted' => 'menghapus',
                            default => $record->action,
                        };

                        $detail = '';
                        if ($record->action === 'updated' && $record->new_values) {
                            $fields = array_keys($record->new_values);
                            $detail = ' ('.implode(', ', array_slice($fields, 0, 3)).(count($fields) > 3 ? '...' : '').')';
                        }

                        return "{$record->user?->name} {$action} {$model} #{$record->model_id}{$detail}";
                    })
                    ->wrap(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_agent')
                    ->label('Browser')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Dibuat',
                        'updated' => 'Diubah',
                        'deleted' => 'Dihapus',
                    ]),
                SelectFilter::make('model_type')
                    ->label('Model')
                    ->options([
                        'App\Models\Sale' => 'Penjualan',
                        'App\Models\Purchase' => 'Pembelian',
                        'App\Models\Product' => 'Produk',
                        'App\Models\User' => 'Pengguna',
                        'App\Models\CashierShift' => 'Shift Kasir',
                        'App\Models\StockOpname' => 'Stock Opname',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('activity-logs.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('activity-logs.delete')),
                ]),
            ]);
    }
}
