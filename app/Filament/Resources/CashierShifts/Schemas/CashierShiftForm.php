<?php

namespace App\Filament\Resources\CashierShifts\Schemas;

use App\Enums\ShiftStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CashierShiftForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                DateTimePicker::make('opening_time')
                    ->required(),
                DateTimePicker::make('closing_time'),
                TextInput::make('opening_cash')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('expected_cash')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('actual_cash')
                    ->numeric()
                    ->default(null)
                    ->prefix('Rp'),
                TextInput::make('difference')
                    ->numeric()
                    ->default(null)
                    ->prefix('Rp'),
                Select::make('status')
                    ->options(ShiftStatus::class)
                    ->default('open')
                    ->required(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
