<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Enums\CategoryType as CategoryTypeEnum;
use App\Models\CategoryType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kategori')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required(),
                        Select::make('category_type_id')
                            ->label('Tipe Kategori')
                            ->relationship('categoryType', 'name')
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required(),
                                TextInput::make('code')
                                    ->label('Kode')
                                    ->required(),
                                Toggle::make('requires_prescription')
                                    ->label('Memerlukan Resep'),
                                Toggle::make('is_narcotic')
                                    ->label('Narkotika'),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $categoryType = CategoryType::find($state);
                                    if ($categoryType) {
                                        $set('requires_prescription', $categoryType->requires_prescription);
                                        $set('is_narcotic', $categoryType->is_narcotic);
                                    }
                                }
                            })
                            ->live(),
                        Select::make('type')
                            ->label('Tipe (Legacy)')
                            ->options(CategoryTypeEnum::class)
                            ->helperText('Field lama, gunakan Tipe Kategori di atas'),
                    ])
                    ->columns(2),

                Section::make('Pengaturan')
                    ->schema([
                        Toggle::make('requires_prescription')
                            ->label('Memerlukan Resep Dokter'),
                        Toggle::make('is_narcotic')
                            ->label('Termasuk Narkotika/Psikotropika'),
                    ])
                    ->columns(2),
            ]);
    }
}
