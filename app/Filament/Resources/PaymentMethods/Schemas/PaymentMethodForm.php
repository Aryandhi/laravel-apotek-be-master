<?php

namespace App\Filament\Resources\PaymentMethods\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentMethodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Metode Pembayaran')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Metode')
                                    ->placeholder('cth: Transfer BCA, Cash, QRIS')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('Kode')
                                    ->placeholder('cth: CASH, TRF-BCA, QRIS')
                                    ->helperText('Kode unik untuk identifikasi')
                                    ->required()
                                    ->maxLength(50),
                            ]),
                    ]),

                Section::make('Detail Rekening')
                    ->description('Isi jika metode pembayaran menggunakan transfer bank')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('account_number')
                                    ->label('Nomor Rekening')
                                    ->placeholder('cth: 1234567890')
                                    ->maxLength(50),
                                TextInput::make('account_name')
                                    ->label('Atas Nama')
                                    ->placeholder('cth: PT Apotek Sehat')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Pengaturan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_cash')
                                    ->label('Pembayaran Tunai')
                                    ->helperText('Aktifkan jika ini metode pembayaran tunai/cash')
                                    ->default(false),
                                Toggle::make('is_active')
                                    ->label('Status Aktif')
                                    ->helperText('Metode tidak aktif tidak akan muncul di pilihan pembayaran')
                                    ->default(true),
                            ]),
                    ]),
            ]);
    }
}
