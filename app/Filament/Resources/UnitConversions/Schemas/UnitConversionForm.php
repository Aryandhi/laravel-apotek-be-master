<?php

namespace App\Filament\Resources\UnitConversions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitConversionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Konversi')
                    ->description('Atur konversi antar satuan untuk produk')
                    ->schema([
                        Select::make('product_id')
                            ->label('Produk')
                            ->relationship('product', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                        Grid::make(3)
                            ->schema([
                                Select::make('from_unit_id')
                                    ->label('Dari Satuan')
                                    ->relationship('fromUnit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('conversion_value')
                                    ->label('Nilai Konversi')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->step(0.0001)
                                    ->helperText('Contoh: 1 Box = 10 Strip, nilai = 10'),
                                Select::make('to_unit_id')
                                    ->label('Ke Satuan')
                                    ->relationship('toUnit', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                            ]),
                    ]),
            ]);
    }
}
