<?php

namespace App\Filament\Resources\CategoryTypes\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tipe Kategori')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('code')
                            ->label('Kode')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->helperText('Kode unik untuk tipe kategori (contoh: obat_bebas)'),
                        TextInput::make('description')
                            ->label('Deskripsi')
                            ->maxLength(255),
                        ColorPicker::make('color')
                            ->label('Warna Badge'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                    ])
                    ->columns(2),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('requires_prescription')
                            ->label('Memerlukan Resep')
                            ->helperText('Produk dengan tipe ini memerlukan resep dokter'),
                        Toggle::make('is_narcotic')
                            ->label('Narkotika/Psikotropika')
                            ->helperText('Produk dengan tipe ini termasuk narkotika atau psikotropika'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }
}
