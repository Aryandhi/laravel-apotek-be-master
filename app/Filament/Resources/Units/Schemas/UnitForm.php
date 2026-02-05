<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Satuan')
                    ->description('Data satuan dasar untuk produk')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Satuan')
                                    ->placeholder('cth: Tablet, Strip, Box')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('code')
                                    ->label('Kode')
                                    ->placeholder('cth: TAB, STRIP, BOX')
                                    ->helperText('Kode singkat untuk satuan (huruf kapital)')
                                    ->required()
                                    ->maxLength(20)
                                    ->extraInputAttributes(['style' => 'text-transform: uppercase']),
                            ]),
                    ]),
            ]);
    }
}
