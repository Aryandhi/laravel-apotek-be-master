<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\CreatePermission;
use App\Filament\Resources\Permissions\Pages\EditPermission;
use App\Filament\Resources\Permissions\Pages\ListPermissions;
use App\Filament\Resources\Permissions\Schemas\PermissionForm;
use App\Filament\Resources\Permissions\Tables\PermissionsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use UnitEnum;

class PermissionResource extends Resource
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('permissions.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('permissions.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('permissions.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('permissions.delete') ?? false;
    }

    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static UnitEnum|string|null $navigationGroup = 'Akses & Pengguna';

    protected static ?string $navigationLabel = 'Hak Akses';

    protected static ?string $modelLabel = 'Hak Akses';

    protected static ?string $pluralModelLabel = 'Hak Akses';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
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
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getPermissionModules(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'reports' => 'Laporan',
            'sales' => 'Penjualan',
            'purchases' => 'Pembelian',
            'products' => 'Produk',
            'product-batches' => 'Batch Produk',
            'stock' => 'Stok',
            'stock-opname' => 'Stock Opname',
            'categories' => 'Kategori',
            'category-types' => 'Tipe Kategori',
            'units' => 'Satuan',
            'suppliers' => 'Supplier',
            'customers' => 'Pelanggan',
            'doctors' => 'Dokter',
            'payment-methods' => 'Metode Pembayaran',
            'users' => 'Pengguna',
            'roles' => 'Role',
            'permissions' => 'Hak Akses',
            'stores' => 'Toko',
            'settings' => 'Pengaturan',
            'cashier' => 'Kasir',
            'xendit' => 'Xendit',
        ];
    }

    public static function getPermissionActions(): array
    {
        return [
            'view' => 'Lihat',
            'create' => 'Tambah',
            'update' => 'Edit',
            'delete' => 'Hapus',
            'export' => 'Ekspor',
            'close' => 'Tutup',
            'approve' => 'Setujui',
            'void' => 'Void',
            'adjustment' => 'Penyesuaian',
            'manage' => 'Kelola',
        ];
    }

    public static function formatPermissionName(string $name): string
    {
        $modules = static::getPermissionModules();
        $actions = static::getPermissionActions();

        $parts = explode('.', $name);
        $module = $parts[0] ?? $name;
        $action = $parts[1] ?? '';

        $moduleName = $modules[$module] ?? ucfirst($module);
        $actionName = $actions[$action] ?? ucfirst($action);

        return $actionName ? "{$moduleName} - {$actionName}" : $moduleName;
    }
}
