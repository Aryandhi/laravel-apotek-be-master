<?php

namespace App\Filament\Pages;

use App\Models\Store;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class StoreSettings extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.view') ?? false;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $navigationLabel = 'Pengaturan Toko';

    protected static ?string $title = 'Pengaturan Toko / Apotek';

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $store = Store::first();

        $this->form->fill([
            'name' => $store?->name ?? '',
            'code' => $store?->code ?? '',
            'address' => $store?->address ?? '',
            'phone' => $store?->phone ?? '',
            'email' => $store?->email ?? '',
            'sia_number' => $store?->sia_number ?? '',
            'sipa_number' => $store?->sipa_number ?? '',
            'pharmacist_name' => $store?->pharmacist_name ?? '',
            'pharmacist_sipa' => $store?->pharmacist_sipa ?? '',
            'logo' => $store?->logo ?? '',
            'receipt_footer' => $store?->receipt_footer ?? 'Terima kasih atas kunjungan Anda',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('name')
                    ->label('Nama Apotek')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Apotek Sehat Selalu'),

                TextInput::make('code')
                    ->label('Kode Toko')
                    ->maxLength(50)
                    ->placeholder('APT001'),

                Textarea::make('address')
                    ->label('Alamat')
                    ->rows(3)
                    ->placeholder('Jl. Kesehatan No. 123, Kota'),

                TextInput::make('phone')
                    ->label('Telepon')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('021-1234567'),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->placeholder('apotek@example.com'),

                TextInput::make('sia_number')
                    ->label('Nomor SIA')
                    ->maxLength(100)
                    ->helperText('Surat Izin Apotek')
                    ->placeholder('SIA.XX.XX.XX.XXX'),

                TextInput::make('sipa_number')
                    ->label('Nomor SIPA')
                    ->maxLength(100)
                    ->helperText('Surat Izin Praktik Apoteker')
                    ->placeholder('SIPA.XX.XX.XX.XXX'),

                TextInput::make('pharmacist_name')
                    ->label('Nama Apoteker')
                    ->maxLength(255)
                    ->placeholder('apt. Nama Apoteker, S.Farm'),

                TextInput::make('pharmacist_sipa')
                    ->label('SIPA Apoteker')
                    ->maxLength(100)
                    ->placeholder('SIPA.XX.XX.XX.XXX'),

                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('store-logos')
                    ->maxSize(1024)
                    ->helperText('Maksimal 1MB, format JPG/PNG'),

                Textarea::make('receipt_footer')
                    ->label('Footer Struk')
                    ->rows(2)
                    ->placeholder('Terima kasih atas kunjungan Anda')
                    ->helperText('Teks yang ditampilkan di bagian bawah struk'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Apotek')
                    ->description('Data apotek yang akan ditampilkan di struk dan dokumen lainnya')
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
        \Log::info('StoreSettings::save() called');

        try {
            $data = $this->form->getState();
            \Log::info('Form data:', $data);

            $store = Store::first();

            if ($store) {
                $store->update($data);
                \Log::info('Store updated', ['id' => $store->id]);
            } else {
                $store = Store::create($data);
                \Log::info('Store created', ['id' => $store->id]);
            }

            Notification::make()
                ->title('Berhasil')
                ->body('Pengaturan toko berhasil disimpan')
                ->success()
                ->send();

            \Log::info('Notification sent');
        } catch (\Exception $e) {
            \Log::error('StoreSettings save error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            Notification::make()
                ->title('Gagal')
                ->body('Terjadi kesalahan: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
