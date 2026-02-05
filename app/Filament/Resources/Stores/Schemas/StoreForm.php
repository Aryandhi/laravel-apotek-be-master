<?php

namespace App\Filament\Resources\Stores\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar')
                    ->description('Data identitas toko/apotek')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Toko/Apotek')
                                    ->required()
                                    ->placeholder('cth: Apotek Sehat Selalu')
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('Kode Toko')
                                    ->required()
                                    ->placeholder('cth: APT001')
                                    ->maxLength(50),
                            ]),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->placeholder('Jl. Kesehatan No. 123, Kota')
                            ->rows(3)
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('phone')
                                    ->label('Telepon')
                                    ->tel()
                                    ->placeholder('cth: 021-1234567')
                                    ->maxLength(20),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->placeholder('cth: apotek@example.com')
                                    ->maxLength(255),
                            ]),
                    ]),

                Section::make('Perizinan')
                    ->description('Data perizinan apotek')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('sia_number')
                                    ->label('Nomor SIA')
                                    ->placeholder('cth: SIA.XX.XX.XX.XXX')
                                    ->helperText('Surat Izin Apotek')
                                    ->maxLength(100),
                                TextInput::make('sipa_number')
                                    ->label('Nomor SIPA')
                                    ->placeholder('cth: SIPA.XX.XX.XX.XXX')
                                    ->helperText('Surat Izin Praktik Apoteker')
                                    ->maxLength(100),
                            ]),
                    ]),

                Section::make('Apoteker Penanggung Jawab')
                    ->description('Data apoteker yang bertanggung jawab')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('pharmacist_name')
                                    ->label('Nama Apoteker')
                                    ->placeholder('cth: apt. Nama Apoteker, S.Farm')
                                    ->maxLength(255),
                                TextInput::make('pharmacist_sipa')
                                    ->label('SIPA Apoteker')
                                    ->placeholder('cth: SIPA.XX.XX.XX.XXX')
                                    ->maxLength(100),
                            ]),
                    ]),

                Section::make('Tampilan & Struk')
                    ->description('Pengaturan tampilan dan struk')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('store-logos')
                            ->maxSize(1024)
                            ->helperText('Maksimal 1MB, format JPG/PNG')
                            ->columnSpanFull(),
                        Textarea::make('receipt_footer')
                            ->label('Footer Struk')
                            ->placeholder('Terima kasih atas kunjungan Anda')
                            ->helperText('Teks yang ditampilkan di bagian bawah struk')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
