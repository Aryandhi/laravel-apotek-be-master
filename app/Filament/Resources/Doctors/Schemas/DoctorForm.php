<?php

namespace App\Filament\Resources\Doctors\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DoctorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dokter')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Dokter')
                                    ->placeholder('cth: dr. Budi Santoso, Sp.PD')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('sip_number')
                                    ->label('Nomor SIP')
                                    ->placeholder('cth: SIP.123/456/2024')
                                    ->helperText('Surat Izin Praktik')
                                    ->maxLength(100),
                                TextInput::make('specialization')
                                    ->label('Spesialisasi')
                                    ->placeholder('cth: Penyakit Dalam, Anak, Umum')
                                    ->maxLength(100),
                                TextInput::make('hospital_clinic')
                                    ->label('Rumah Sakit / Klinik')
                                    ->placeholder('cth: RS Medika, Klinik Sehat')
                                    ->maxLength(255),
                            ]),
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
                            ]),
                        Textarea::make('address')
                            ->label('Alamat Praktik')
                            ->placeholder('Alamat lengkap tempat praktik')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->helperText('Dokter tidak aktif tidak akan muncul di pilihan resep')
                            ->default(true),
                    ]),
            ]);
    }
}
