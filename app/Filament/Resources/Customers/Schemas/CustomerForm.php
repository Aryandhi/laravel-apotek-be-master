<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pelanggan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Pelanggan')
                                    ->placeholder('cth: Budi Santoso')
                                    ->required()
                                    ->maxLength(255),
                                DatePicker::make('birth_date')
                                    ->label('Tanggal Lahir')
                                    ->placeholder('Pilih tanggal lahir')
                                    ->displayFormat('d M Y')
                                    ->native(false),
                            ]),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->placeholder('Alamat lengkap pelanggan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Kontak')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->placeholder('cth: 081234567890')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->placeholder('cth: pelanggan@email.com')
                                    ->email()
                                    ->maxLength(255),
                            ]),
                    ]),

                // Section::make('Poin Member')
                //     ->description('Poin dapat digunakan untuk redeem hadiah atau diskon')
                //     ->schema([
                //         TextInput::make('points')
                //             ->label('Jumlah Poin')
                //             ->numeric()
                //             ->default(0)
                //             ->minValue(0)
                //             ->helperText('Poin terakumulasi dari setiap transaksi pembelian'),
                //     ])
                //     ->collapsed(),
            ]);
    }
}
