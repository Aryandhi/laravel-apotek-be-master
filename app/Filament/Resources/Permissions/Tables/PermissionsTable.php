<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PermissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kode Permission')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('display_name')
                    ->label('Nama')
                    ->getStateUsing(fn ($record) => PermissionResource::formatPermissionName($record->name))
                    ->searchable(query: function ($query, string $search) {
                        // Search in the formatted name by searching the raw name
                        return $query->where('name', 'like', "%{$search}%");
                    }),
                TextColumn::make('module')
                    ->label('Modul')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        $module = explode('.', $record->name)[0] ?? 'other';

                        return PermissionResource::getPermissionModules()[$module] ?? ucfirst($module);
                    }),
                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Lihat' => 'success',
                        'Tambah' => 'info',
                        'Ubah' => 'warning',
                        'Hapus' => 'danger',
                        default => 'gray',
                    })
                    ->getStateUsing(function ($record) {
                        $action = explode('.', $record->name)[1] ?? '';

                        return PermissionResource::getPermissionActions()[$action] ?? ucfirst($action);
                    }),
                TextColumn::make('roles_count')
                    ->label('Digunakan')
                    ->counts('roles')
                    ->badge()
                    ->color('success')
                    ->suffix(' role')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('module')
                    ->label('Modul')
                    ->options(PermissionResource::getPermissionModules())
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            return $query->where('name', 'like', $data['value'].'.%');
                        }

                        return $query;
                    }),
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options(PermissionResource::getPermissionActions())
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            return $query->where('name', 'like', '%.'.$data['value']);
                        }

                        return $query;
                    }),
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(fn () => \Spatie\Permission\Models\Role::pluck('name', 'id')->toArray())
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            return $query->whereHas('roles', fn ($q) => $q->where('id', $data['value']));
                        }

                        return $query;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('permissions.update')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('permissions.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('permissions.delete')),
                ]),
            ]);
    }
}
