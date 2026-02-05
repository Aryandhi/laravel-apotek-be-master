<?php

namespace App\Filament\Resources\Permissions\Schemas;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Hak Akses')
                    ->description('Kelola hak akses untuk mengontrol fitur yang dapat digunakan oleh role tertentu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('module')
                                    ->label('Modul')
                                    ->options(PermissionResource::getPermissionModules())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $action = $get('action');
                                        if ($state && $action) {
                                            $set('name', "{$state}.{$action}");
                                        }
                                    })
                                    ->dehydrated(false),
                                Select::make('action')
                                    ->label('Aksi')
                                    ->options(PermissionResource::getPermissionActions())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $module = $get('module');
                                        if ($module && $state) {
                                            $set('name', "{$module}.{$state}");
                                        }
                                    })
                                    ->dehydrated(false),
                            ]),
                        TextInput::make('name')
                            ->label('Nama Permission')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Format otomatis: modul.aksi (contoh: products.view)')
                            ->readOnly(),
                    ]),
            ]);
    }
}
