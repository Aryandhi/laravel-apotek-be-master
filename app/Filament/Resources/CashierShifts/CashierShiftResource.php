<?php

namespace App\Filament\Resources\CashierShifts;

use App\Filament\Resources\CashierShifts\Pages\EditCashierShift;
use App\Filament\Resources\CashierShifts\Pages\ListCashierShifts;
use App\Filament\Resources\CashierShifts\Schemas\CashierShiftForm;
use App\Filament\Resources\CashierShifts\Tables\CashierShiftsTable;
use App\Models\CashierShift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CashierShiftResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('cashier-shifts.view') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('cashier-shifts.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('cashier-shifts.delete') ?? false;
    }

    protected static ?string $model = CashierShift::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static UnitEnum|string|null $navigationGroup = 'Penjualan';

    protected static ?string $navigationLabel = 'Shift Kasir';

    protected static ?string $modelLabel = 'Shift Kasir';

    protected static ?string $pluralModelLabel = 'Shift Kasir';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CashierShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashierShiftsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashierShifts::route('/'),
            'edit' => EditCashierShift::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
