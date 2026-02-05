<?php

namespace App\Filament\Resources\XenditTransactions;

use App\Filament\Resources\XenditTransactions\Pages\ListXenditTransactions;
use App\Filament\Resources\XenditTransactions\Pages\ViewXenditTransaction;
use App\Filament\Resources\XenditTransactions\Tables\XenditTransactionsTable;
use App\Models\XenditTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class XenditTransactionResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('xendit.view') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return false; // Xendit transactions should not be edited
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Xendit transactions should not be deleted
    }

    protected static ?string $model = XenditTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static UnitEnum|string|null $navigationGroup = 'Penjualan';

    protected static ?string $navigationLabel = 'Transaksi Xendit';

    protected static ?string $modelLabel = 'Transaksi Xendit';

    protected static ?string $pluralModelLabel = 'Transaksi Xendit';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return XenditTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListXenditTransactions::route('/'),
            'view' => ViewXenditTransaction::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
