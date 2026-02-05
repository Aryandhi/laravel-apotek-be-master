<?php

namespace App\Filament\Resources\Sales\Schemas;

use App\Enums\SaleStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->default(null),
                Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->default(null),
                TextInput::make('prescription_number')
                    ->default(null),
                Toggle::make('is_prescription')
                    ->required(),
                TextInput::make('patient_name')
                    ->default(null),
                Textarea::make('patient_address')
                    ->default(null)
                    ->columnSpanFull(),
                DatePicker::make('date')
                    ->required(),
                TextInput::make('subtotal')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('discount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('tax')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('total')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('paid_amount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                TextInput::make('change_amount')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->prefix('Rp'),
                Select::make('status')
                    ->options(SaleStatus::class)
                    ->default('completed')
                    ->required(),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->default(null),
                Select::make('shift_id')
                    ->relationship('shift', 'id')
                    ->default(null),
            ]);
    }
}
