<?php

namespace App\Filament\Resources\PurchasePlans;

use App\Filament\Resources\PurchasePlans\Pages\ListPurchasePlans;
use App\Filament\Resources\PurchasePlans\Tables\PurchasePlansTable;
use App\Models\Product;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class PurchasePlanResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('purchase-plans.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canDelete(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static UnitEnum|string|null $navigationGroup = 'Pembelian';

    protected static ?string $navigationLabel = 'Perencanaan';

    protected static ?string $modelLabel = 'Perencanaan';

    protected static ?string $pluralModelLabel = 'Perencanaan';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'perencanaan-pesanan/perencanaan';

    public static function table(Table $table): Table
    {
        return PurchasePlansTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchasePlans::route('/'),
        ];
    }
}
