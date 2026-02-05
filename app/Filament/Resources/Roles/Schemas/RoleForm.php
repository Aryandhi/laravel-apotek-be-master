<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        // Get all permissions grouped by module for display
        $allPermissions = Permission::orderBy('name')->get();
        $options = $allPermissions->pluck('name', 'id')
            ->map(fn ($name) => self::formatPermissionName($name))
            ->toArray();

        return $schema
            ->components([
                Section::make('Informasi Role')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Role')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('cth: Manager, Supervisor'),
                    ])
                    ->columnSpanFull(),

                Section::make('Hak Akses (Permissions)')
                    ->description('Centang permission yang dimiliki role ini')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('')
                            ->relationship('permissions', 'name')
                            ->options($options)
                            ->columns(3)
                            ->bulkToggleable()
                            ->searchable()
                            ->gridDirection('row'),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    private static function formatPermissionName(string $name): string
    {
        $translations = [
            'view' => 'Lihat',
            'create' => 'Tambah',
            'update' => 'Edit',
            'delete' => 'Hapus',
            'approve' => 'Approve',
            'void' => 'Void/Batal',
            'close' => 'Tutup',
            'export' => 'Export',
            'adjustment' => 'Penyesuaian',
            'manage' => 'Kelola',
            'sales' => 'Penjualan',
            'stock' => 'Stok',
            'purchases' => 'Pembelian',
            'financial' => 'Keuangan',
        ];

        $modules = [
            'dashboard' => 'Dashboard',
            'sales' => 'Penjualan',
            'sale-returns' => 'Retur Jual',
            'purchases' => 'Pembelian',
            'purchase-returns' => 'Retur Beli',
            'products' => 'Produk',
            'categories' => 'Kategori',
            'units' => 'Satuan',
            'stock' => 'Stok',
            'stock-opname' => 'Stok Opname',
            'customers' => 'Pelanggan',
            'doctors' => 'Dokter',
            'suppliers' => 'Supplier',
            'payment-methods' => 'Metode Bayar',
            'stores' => 'Toko',
            'users' => 'Pengguna',
            'roles' => 'Role',
            'settings' => 'Pengaturan',
            'reports' => 'Laporan',
            'cashier-shifts' => 'Shift Kasir',
            'activity-logs' => 'Log Aktivitas',
            'xendit' => 'Xendit',
        ];

        $parts = explode('.', $name);
        $module = $modules[$parts[0]] ?? ucfirst($parts[0]);
        $action = $translations[$parts[1] ?? ''] ?? ucfirst($parts[1] ?? '');

        return "[{$module}] {$action}";
    }
}
