<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Sistem')
                    ->description('Konfigurasi pengaturan aplikasi')
                    ->schema([
                        Select::make('store_id')
                            ->label('Toko')
                            ->relationship('store', 'name')
                            ->placeholder('Global (Semua Toko)')
                            ->helperText('Kosongkan untuk pengaturan global')
                            ->default(null),
                        TextInput::make('group')
                            ->label('Grup')
                            ->required()
                            ->placeholder('cth: general, invoice, receipt')
                            ->helperText('Kategori pengaturan')
                            ->default('general'),
                        TextInput::make('key')
                            ->label('Kunci')
                            ->required()
                            ->placeholder('cth: app_name, tax_rate')
                            ->helperText('Nama unik pengaturan'),
                        Textarea::make('value')
                            ->label('Nilai')
                            ->placeholder('Masukkan nilai pengaturan')
                            ->rows(3)
                            ->default(null)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
