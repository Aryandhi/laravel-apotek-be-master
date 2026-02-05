<?php

namespace App\Filament\Resources\CategoryTypes;

use App\Filament\Resources\CategoryTypes\Pages\CreateCategoryType;
use App\Filament\Resources\CategoryTypes\Pages\EditCategoryType;
use App\Filament\Resources\CategoryTypes\Pages\ListCategoryTypes;
use App\Filament\Resources\CategoryTypes\Schemas\CategoryTypeForm;
use App\Filament\Resources\CategoryTypes\Tables\CategoryTypesTable;
use App\Models\CategoryType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class CategoryTypeResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('categories.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('categories.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('categories.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('categories.delete') ?? false;
    }

    protected static ?string $model = CategoryType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Tipe Kategori';

    protected static ?string $modelLabel = 'Tipe Kategori';

    protected static ?string $pluralModelLabel = 'Tipe Kategori';

    protected static ?int $navigationSort = 0;

    public static function form(Schema $schema): Schema
    {
        return CategoryTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoryTypesTable::configure($table);
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
            'index' => ListCategoryTypes::route('/'),
            'create' => CreateCategoryType::route('/create'),
            'edit' => EditCategoryType::route('/{record}/edit'),
        ];
    }
}
