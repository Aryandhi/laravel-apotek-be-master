<?php

namespace App\Filament\Resources\UnitConversions;

use App\Filament\Resources\UnitConversions\Pages\CreateUnitConversion;
use App\Filament\Resources\UnitConversions\Pages\EditUnitConversion;
use App\Filament\Resources\UnitConversions\Pages\ListUnitConversions;
use App\Filament\Resources\UnitConversions\Schemas\UnitConversionForm;
use App\Filament\Resources\UnitConversions\Tables\UnitConversionsTable;
use App\Models\UnitConversion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class UnitConversionResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('units.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('units.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('units.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('units.delete') ?? false;
    }

    protected static ?string $model = UnitConversion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Konversi Satuan';

    protected static ?string $modelLabel = 'Konversi Satuan';

    protected static ?string $pluralModelLabel = 'Konversi Satuan';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return UnitConversionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UnitConversionsTable::configure($table);
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
            'index' => ListUnitConversions::route('/'),
            'create' => CreateUnitConversion::route('/create'),
            'edit' => EditUnitConversion::route('/{record}/edit'),
        ];
    }
}
