<?php

namespace App\Filament\Resources\ActivityLogs\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Aktivitas')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Select::make('user_id')
                                    ->label('User')
                                    ->relationship('user', 'name')
                                    ->disabled(),
                                TextInput::make('action')
                                    ->label('Aksi')
                                    ->disabled()
                                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                                        'created' => 'Dibuat',
                                        'updated' => 'Diubah',
                                        'deleted' => 'Dihapus',
                                        default => $state ?? '-',
                                    }),
                            ]),
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('model_display')
                                    ->label('Model')
                                    ->content(fn ($record) => self::getModelLabel($record?->model_type)),
                                TextInput::make('model_id')
                                    ->label('ID Record')
                                    ->disabled(),
                            ]),
                        Placeholder::make('record_info')
                            ->label('Data yang Diubah')
                            ->content(fn ($record) => self::getRecordDescription($record))
                            ->columnSpanFull(),
                        Grid::make(2)
                            ->schema([
                                TextInput::make('ip_address')
                                    ->label('IP Address')
                                    ->disabled(),
                                TextInput::make('created_at')
                                    ->label('Waktu')
                                    ->disabled()
                                    ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y H:i:s') : null),
                            ]),
                    ]),

                Section::make('Detail Perubahan')
                    ->schema([
                        Placeholder::make('changes_summary')
                            ->label('Ringkasan Perubahan')
                            ->content(fn ($record) => self::getChangesSummary($record))
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record?->old_values) || ! empty($record?->new_values)),
                        Placeholder::make('old_values_display')
                            ->label('Nilai Sebelum')
                            ->content(fn ($record) => self::renderValuesTable($record?->old_values))
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record?->old_values)),
                        Placeholder::make('new_values_display')
                            ->label('Nilai Sesudah')
                            ->content(fn ($record) => self::renderValuesTable($record?->new_values))
                            ->columnSpanFull()
                            ->visible(fn ($record) => ! empty($record?->new_values)),
                        Placeholder::make('no_changes')
                            ->label('')
                            ->content('Tidak ada detail perubahan')
                            ->visible(fn ($record) => empty($record?->old_values) && empty($record?->new_values)),
                    ])
                    ->collapsible(),

                Section::make('Informasi Teknis')
                    ->schema([
                        Placeholder::make('user_agent_display')
                            ->label('Browser / User Agent')
                            ->content(fn ($record) => $record?->user_agent ?? '-'),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    private static function getModelLabel(?string $modelType): string
    {
        if (! $modelType) {
            return '-';
        }

        $labels = [
            'App\\Models\\Product' => 'Produk',
            'App\\Models\\ProductBatch' => 'Batch Produk',
            'App\\Models\\Category' => 'Kategori',
            'App\\Models\\CategoryType' => 'Tipe Kategori',
            'App\\Models\\Unit' => 'Satuan',
            'App\\Models\\UnitConversion' => 'Konversi Satuan',
            'App\\Models\\Supplier' => 'Supplier',
            'App\\Models\\Customer' => 'Pelanggan',
            'App\\Models\\Doctor' => 'Dokter',
            'App\\Models\\PaymentMethod' => 'Metode Pembayaran',
            'App\\Models\\Sale' => 'Penjualan',
            'App\\Models\\SaleItem' => 'Item Penjualan',
            'App\\Models\\SaleReturn' => 'Retur Penjualan',
            'App\\Models\\Purchase' => 'Pembelian',
            'App\\Models\\PurchaseItem' => 'Item Pembelian',
            'App\\Models\\PurchaseReturn' => 'Retur Pembelian',
            'App\\Models\\StockMovement' => 'Mutasi Stok',
            'App\\Models\\StockOpname' => 'Stock Opname',
            'App\\Models\\StockOpnameItem' => 'Item Stock Opname',
            'App\\Models\\CashierShift' => 'Shift Kasir',
            'App\\Models\\User' => 'Pengguna',
            'App\\Models\\Store' => 'Toko',
            'App\\Models\\Setting' => 'Pengaturan',
        ];

        return $labels[$modelType] ?? class_basename($modelType);
    }

    private static function getRecordDescription($record): string
    {
        if (! $record || ! $record->model_type || ! $record->model_id) {
            return '-';
        }

        try {
            $model = $record->model_type::find($record->model_id);

            if (! $model) {
                // Record might be deleted, try to get info from old/new values
                $values = $record->new_values ?? $record->old_values ?? [];

                return self::extractNameFromValues($values, $record->model_type);
            }

            return self::getModelDisplayName($model, $record->model_type);
        } catch (\Exception $e) {
            return '-';
        }
    }

    private static function getModelDisplayName($model, string $modelType): string
    {
        $className = class_basename($modelType);

        return match ($className) {
            'Product' => $model->name ?? $model->code ?? "Produk #{$model->id}",
            'ProductBatch' => ($model->product?->name ?? 'Produk')." - Batch: {$model->batch_number}",
            'Category' => $model->name ?? "Kategori #{$model->id}",
            'CategoryType' => $model->name ?? "Tipe Kategori #{$model->id}",
            'Unit' => $model->name ?? "Satuan #{$model->id}",
            'UnitConversion' => ($model->product?->name ?? 'Produk').": {$model->fromUnit?->name} → {$model->toUnit?->name}",
            'Supplier' => $model->name ?? "Supplier #{$model->id}",
            'Customer' => $model->name ?? "Pelanggan #{$model->id}",
            'Doctor' => $model->name ?? "Dokter #{$model->id}",
            'PaymentMethod' => $model->name ?? "Metode Pembayaran #{$model->id}",
            'Sale' => 'Invoice: '.($model->invoice_number ?? "#{$model->id}"),
            'SaleReturn' => 'Retur: '.($model->return_number ?? "#{$model->id}"),
            'Purchase' => 'PO: '.($model->invoice_number ?? "#{$model->id}"),
            'PurchaseReturn' => 'Retur: '.($model->return_number ?? "#{$model->id}"),
            'StockMovement' => ($model->productBatch?->product?->name ?? 'Produk')." - {$model->type?->label()}",
            'StockOpname' => 'Opname: '.($model->code ?? "#{$model->id}"),
            'StockOpnameItem' => ($model->product?->name ?? 'Produk').' - Batch: '.($model->productBatch?->batch_number ?? '-'),
            'CashierShift' => 'Shift: '.($model->shift_number ?? "#{$model->id}"),
            'User' => $model->name ?? "User #{$model->id}",
            'Store' => $model->name ?? "Toko #{$model->id}",
            'Setting' => $model->key ?? "Setting #{$model->id}",
            default => "#{$model->id}",
        };
    }

    private static function extractNameFromValues(array $values, string $modelType): string
    {
        $className = class_basename($modelType);

        // Try to find identifiable field
        if (isset($values['name'])) {
            return $values['name'];
        }
        if (isset($values['code'])) {
            return "Kode: {$values['code']}";
        }
        if (isset($values['invoice_number'])) {
            return "Invoice: {$values['invoice_number']}";
        }
        if (isset($values['batch_number'])) {
            return "Batch: {$values['batch_number']}";
        }

        return "Data {$className}";
    }

    private static function getChangesSummary($record): string
    {
        if (! $record) {
            return '-';
        }

        $oldValues = $record->old_values ?? [];
        $newValues = $record->new_values ?? [];

        $changes = [];

        // For created action
        if ($record->action === 'created' && ! empty($newValues)) {
            return 'Data baru dibuat dengan '.count($newValues).' field';
        }

        // For deleted action
        if ($record->action === 'deleted' && ! empty($oldValues)) {
            return 'Data dihapus dengan '.count($oldValues).' field';
        }

        // For updated action
        $changedFields = array_keys(array_merge($oldValues, $newValues));

        foreach ($changedFields as $field) {
            $old = $oldValues[$field] ?? null;
            $new = $newValues[$field] ?? null;

            if ($old !== $new) {
                $fieldLabel = self::getFieldLabel($field);
                $changes[] = $fieldLabel;
            }
        }

        if (empty($changes)) {
            return 'Tidak ada perubahan signifikan';
        }

        return 'Field yang berubah: '.implode(', ', $changes);
    }

    private static function getFieldLabel(string $field): string
    {
        $labels = [
            'name' => 'Nama',
            'code' => 'Kode',
            'status' => 'Status',
            'price' => 'Harga',
            'purchase_price' => 'Harga Beli',
            'selling_price' => 'Harga Jual',
            'stock' => 'Stok',
            'quantity' => 'Jumlah',
            'total' => 'Total',
            'discount' => 'Diskon',
            'tax' => 'Pajak',
            'notes' => 'Catatan',
            'description' => 'Deskripsi',
            'address' => 'Alamat',
            'phone' => 'Telepon',
            'email' => 'Email',
            'is_active' => 'Status Aktif',
            'expired_date' => 'Tgl Kadaluarsa',
            'approved_by' => 'Disetujui Oleh',
            'approved_at' => 'Tgl Persetujuan',
            'updated_at' => 'Tgl Update',
            'created_at' => 'Tgl Dibuat',
        ];

        return $labels[$field] ?? str_replace('_', ' ', ucfirst($field));
    }

    private static function translateStatus($status): string
    {
        if (! is_string($status)) {
            return (string) $status;
        }

        $translations = [
            // Stock Opname Status
            'draft' => 'Draft',
            'in_progress' => 'Sedang Proses',
            'pending_approval' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'cancelled' => 'Dibatalkan',
            // Batch Status
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif',
            'expired' => 'Kadaluarsa',
            // Purchase/Sale Status
            'pending' => 'Pending',
            'completed' => 'Selesai',
            'partial' => 'Sebagian',
            // General Status
            'open' => 'Buka',
            'closed' => 'Tutup',
        ];

        return $translations[$status] ?? $status;
    }

    private static function renderValuesTable($values): HtmlString
    {
        if (! is_array($values) || empty($values)) {
            return new HtmlString('<span class="text-gray-500">-</span>');
        }

        $rows = [];
        foreach ($values as $key => $value) {
            $label = self::getFieldLabel($key);

            // Resolve foreign key IDs to names or translate status values
            $displayValue = self::resolveValue($key, $value);

            // Translate status values
            if ($key === 'status') {
                $displayValue = self::translateStatus($displayValue);
            }

            // Format the value for display
            if (is_bool($displayValue)) {
                $displayValue = $displayValue ? 'Ya' : 'Tidak';
            } elseif (is_null($displayValue)) {
                $displayValue = '-';
            } elseif (is_array($displayValue)) {
                $displayValue = json_encode($displayValue);
            }

            $rows[] = sprintf(
                '<tr><td class="py-1 pr-4 text-gray-500 dark:text-gray-400 font-medium">%s</td><td class="py-1">%s</td></tr>',
                e($label),
                e((string) $displayValue)
            );
        }

        return new HtmlString(
            '<table class="text-sm"><tbody>'.implode('', $rows).'</tbody></table>'
        );
    }

    private static function resolveValue(string $key, $value)
    {
        if (empty($value) || ! is_numeric($value)) {
            return $value;
        }

        try {
            return match ($key) {
                'user_id', 'approved_by', 'created_by', 'updated_by', 'cashier_id' => self::resolveUser($value),
                'product_id' => self::resolveProduct($value),
                'product_batch_id' => self::resolveProductBatch($value),
                'category_id' => self::resolveCategory($value),
                'supplier_id' => self::resolveSupplier($value),
                'customer_id' => self::resolveCustomer($value),
                'doctor_id' => self::resolveDoctor($value),
                'store_id' => self::resolveStore($value),
                'payment_method_id' => self::resolvePaymentMethod($value),
                'base_unit_id', 'from_unit_id', 'to_unit_id' => self::resolveUnit($value),
                default => $value,
            };
        } catch (\Exception $e) {
            return $value;
        }
    }

    private static function resolveUser($id): string
    {
        $user = \App\Models\User::find($id);

        return $user ? $user->name : "User #{$id}";
    }

    private static function resolveProduct($id): string
    {
        $product = \App\Models\Product::find($id);

        return $product ? $product->name : "Produk #{$id}";
    }

    private static function resolveProductBatch($id): string
    {
        $batch = \App\Models\ProductBatch::with('product')->find($id);

        return $batch ? ($batch->product?->name ?? 'Produk')." - {$batch->batch_number}" : "Batch #{$id}";
    }

    private static function resolveCategory($id): string
    {
        $category = \App\Models\Category::find($id);

        return $category ? $category->name : "Kategori #{$id}";
    }

    private static function resolveSupplier($id): string
    {
        $supplier = \App\Models\Supplier::find($id);

        return $supplier ? $supplier->name : "Supplier #{$id}";
    }

    private static function resolveCustomer($id): string
    {
        $customer = \App\Models\Customer::find($id);

        return $customer ? $customer->name : "Pelanggan #{$id}";
    }

    private static function resolveDoctor($id): string
    {
        $doctor = \App\Models\Doctor::find($id);

        return $doctor ? $doctor->name : "Dokter #{$id}";
    }

    private static function resolveStore($id): string
    {
        $store = \App\Models\Store::find($id);

        return $store ? $store->name : "Toko #{$id}";
    }

    private static function resolvePaymentMethod($id): string
    {
        $method = \App\Models\PaymentMethod::find($id);

        return $method ? $method->name : "Metode #{$id}";
    }

    private static function resolveUnit($id): string
    {
        $unit = \App\Models\Unit::find($id);

        return $unit ? $unit->name : "Satuan #{$id}";
    }
}
