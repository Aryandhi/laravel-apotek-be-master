<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class InventorySettings extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.view') ?? false;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $navigationLabel = 'Pengaturan Inventory';

    protected static ?string $title = 'Pengaturan Inventory & Stok';

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 2;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'low_stock_threshold' => Setting::get('low_stock_threshold', 10),
            'near_expired_days' => Setting::get('near_expired_days', 90),
            'enable_batch_tracking' => Setting::get('enable_batch_tracking', true) === 'true' || Setting::get('enable_batch_tracking', true) === true,
            'enable_expired_tracking' => Setting::get('enable_expired_tracking', true) === 'true' || Setting::get('enable_expired_tracking', true) === true,
            'auto_deduct_stock' => Setting::get('auto_deduct_stock', true) === 'true' || Setting::get('auto_deduct_stock', true) === true,
            'fifo_method' => Setting::get('fifo_method', 'fefo'),
            'notify_low_stock' => Setting::get('notify_low_stock', true) === 'true' || Setting::get('notify_low_stock', true) === true,
            'notify_near_expired' => Setting::get('notify_near_expired', true) === 'true' || Setting::get('notify_near_expired', true) === true,
            'notify_expired' => Setting::get('notify_expired', true) === 'true' || Setting::get('notify_expired', true) === true,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('low_stock_threshold')
                    ->label('Ambang Batas Stok Minimum')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->suffix('unit')
                    ->helperText('Produk dengan stok di bawah nilai ini akan ditandai sebagai "Stok Rendah"'),

                TextInput::make('near_expired_days')
                    ->label('Ambang Batas Hampir Kadaluarsa')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->suffix('hari')
                    ->helperText('Produk yang kadaluarsa dalam waktu kurang dari nilai ini akan ditandai sebagai "Hampir Kadaluarsa"'),

                Select::make('fifo_method')
                    ->label('Metode Pengurangan Stok')
                    ->options([
                        'fefo' => 'FEFO (First Expired, First Out) - Kadaluarsa lebih dulu, keluar lebih dulu',
                        'fifo' => 'FIFO (First In, First Out) - Masuk lebih dulu, keluar lebih dulu',
                    ])
                    ->required()
                    ->helperText('Metode yang digunakan saat mengurangi stok otomatis'),

                Toggle::make('enable_batch_tracking')
                    ->label('Aktifkan Pelacakan Batch')
                    ->helperText('Lacak stok berdasarkan nomor batch'),

                Toggle::make('enable_expired_tracking')
                    ->label('Aktifkan Pelacakan Kadaluarsa')
                    ->helperText('Lacak tanggal kadaluarsa produk'),

                Toggle::make('auto_deduct_stock')
                    ->label('Kurangi Stok Otomatis')
                    ->helperText('Stok otomatis berkurang saat transaksi penjualan'),

                Toggle::make('notify_low_stock')
                    ->label('Notifikasi Stok Rendah')
                    ->helperText('Tampilkan notifikasi ketika stok produk rendah'),

                Toggle::make('notify_near_expired')
                    ->label('Notifikasi Hampir Kadaluarsa')
                    ->helperText('Tampilkan notifikasi ketika produk hampir kadaluarsa'),

                Toggle::make('notify_expired')
                    ->label('Notifikasi Kadaluarsa')
                    ->helperText('Tampilkan notifikasi ketika produk sudah kadaluarsa'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pengaturan Stok')
                    ->description('Konfigurasi ambang batas dan metode pengelolaan stok')
                    ->schema([
                        EmbeddedSchema::make('form'),
                    ])
                    ->columns(2)
                    ->footerActions([
                        Action::make('save')
                            ->label('Simpan Pengaturan')
                            ->icon('heroicon-o-check')
                            ->color('success')
                            ->action('save'),
                    ])
                    ->footerActionsAlignment(Alignment::End),
            ]);
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            foreach ($data as $key => $value) {
                // Convert boolean to string for storage
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                Setting::set($key, $value, null, 'inventory');
            }

            Notification::make()
                ->title('Berhasil')
                ->body('Pengaturan inventory berhasil disimpan')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal')
                ->body('Terjadi kesalahan: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
