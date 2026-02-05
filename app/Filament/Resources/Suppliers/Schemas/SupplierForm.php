<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Supplier')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Kode Supplier')
                                    ->placeholder('cth: SUP001')
                                    ->helperText('Kode unik untuk identifikasi supplier')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('name')
                                    ->label('Nama Supplier')
                                    ->placeholder('cth: PT Kimia Farma')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->placeholder('Alamat lengkap supplier')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kontak')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('contact_person')
                                    ->label('Nama Kontak')
                                    ->placeholder('cth: Budi Santoso')
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->placeholder('cth: 021-12345678')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->placeholder('cth: supplier@email.com')
                                    ->email()
                                    ->maxLength(255),
                                TextInput::make('npwp')
                                    ->label('NPWP')
                                    ->placeholder('cth: 12.345.678.9-012.345')
                                    ->helperText('Nomor Pokok Wajib Pajak')
                                    ->maxLength(30),
                            ]),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Supplier tidak aktif tidak akan muncul di pilihan pembelian')
                            ->default(true),
                    ]),
            ]);
    }
}
