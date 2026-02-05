<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pengguna')
                    ->description('Data dasar pengguna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Lengkap')
                                    ->placeholder('cth: Budi Santoso')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->placeholder('cth: budi@email.com')
                                    ->email()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->placeholder('cth: 081234567890')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn (string $operation): bool => $operation === 'create')
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->helperText(fn (string $operation): string => $operation === 'edit' ? 'Kosongkan jika tidak ingin mengubah password' : 'Minimal 8 karakter'),
                            ]),
                    ]),

                Section::make('Pengaturan Akses')
                    ->description('Role dan toko pengguna')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('roles')
                                    ->label('Role')
                                    ->multiple()
                                    ->relationship('roles', 'name')
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Pilih satu atau lebih role untuk pengguna ini'),
                                Select::make('store_id')
                                    ->label('Toko')
                                    ->relationship('store', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Toko tempat pengguna bekerja'),
                            ]),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Pengguna tidak aktif tidak dapat login ke sistem')
                            ->default(true),
                        // Legacy field - hidden but kept for backward compatibility
                        Select::make('role')
                            ->options(UserRole::class)
                            ->default('cashier')
                            ->hidden()
                            ->dehydrated(),
                    ]),
            ]);
    }
}
