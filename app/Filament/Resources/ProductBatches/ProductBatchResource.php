<?php

namespace App\Filament\Resources\ProductBatches;

use App\Filament\Resources\ProductBatches\Pages\CreateProductBatch;
use App\Filament\Resources\ProductBatches\Pages\EditProductBatch;
use App\Filament\Resources\ProductBatches\Pages\ListProductBatches;
use App\Filament\Resources\ProductBatches\Schemas\ProductBatchForm;
use App\Filament\Resources\ProductBatches\Tables\ProductBatchesTable;
use App\Models\ProductBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ProductBatchResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('stock.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('stock.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('stock.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('stock.delete') ?? false;
    }

    protected static ?string $model = ProductBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Batch Produk';

    protected static ?string $modelLabel = 'Batch Produk';

    protected static ?string $pluralModelLabel = 'Batch Produk';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ProductBatchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductBatchesTable::configure($table);
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
            'index' => ListProductBatches::route('/'),
            'create' => CreateProductBatch::route('/create'),
            'edit' => EditProductBatch::route('/{record}/edit'),
        ];
    }
}
