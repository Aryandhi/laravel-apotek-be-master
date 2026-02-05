<?php

namespace App\Filament\Resources\SaleReturns;

use App\Filament\Resources\SaleReturns\Pages\CreateSaleReturn;
use App\Filament\Resources\SaleReturns\Pages\EditSaleReturn;
use App\Filament\Resources\SaleReturns\Pages\ListSaleReturns;
use App\Filament\Resources\SaleReturns\Schemas\SaleReturnForm;
use App\Filament\Resources\SaleReturns\Tables\SaleReturnsTable;
use App\Models\SaleReturn;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SaleReturnResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('sale-returns.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('sale-returns.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('sale-returns.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('sale-returns.delete') ?? false;
    }

    protected static ?string $model = SaleReturn::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptRefund;

    protected static UnitEnum|string|null $navigationGroup = 'Penjualan';

    protected static ?string $navigationLabel = 'Retur Penjualan';

    protected static ?string $modelLabel = 'Retur Penjualan';

    protected static ?string $pluralModelLabel = 'Retur Penjualan';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SaleReturnForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SaleReturnsTable::configure($table);
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
            'index' => ListSaleReturns::route('/'),
            'create' => CreateSaleReturn::route('/create'),
            'edit' => EditSaleReturn::route('/{record}/edit'),
        ];
    }
}
