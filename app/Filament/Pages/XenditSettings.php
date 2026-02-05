<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Services\XenditService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
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

class XenditSettings extends Page
{
    public static function canAccess(): bool
    {
        return auth()->user()?->can('settings.view') ?? false;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Xendit Payment';

    protected static ?string $title = 'Pengaturan Xendit';

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?int $navigationSort = 4;

    public ?array $data = [];

    public bool $isConnected = false;

    public ?string $connectionMessage = null;

    public ?array $connectionData = null;

    public function mount(): void
    {
        // Load from database settings, fallback to config
        $this->form->fill([
            'enabled' => $this->getBoolSetting('xendit_enabled', config('xendit.enabled', false)),
            'secret_key' => Setting::get('xendit_secret_key', config('xendit.secret_key')),
            'webhook_token' => Setting::get('xendit_webhook_token', config('xendit.webhook_token')),
            'is_production' => $this->getBoolSetting('xendit_is_production', config('xendit.is_production', false)),
            'invoice_duration' => (int) Setting::get('xendit_invoice_duration', config('xendit.invoice.duration', 3600)),
        ]);
    }

    private function getBoolSetting(string $key, $default = false): bool
    {
        $value = Setting::get($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        return $value === 'true' || $value === '1' || $value === true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                TextInput::make('secret_key')
                    ->label('Secret Key')
                    ->password()
                    ->revealable()
                    ->helperText('Secret key dari Xendit Dashboard (xnd_development_xxx atau xnd_production_xxx)')
                    ->required(),

                TextInput::make('webhook_token')
                    ->label('Webhook Verification Token')
                    ->password()
                    ->revealable()
                    ->helperText('Token untuk verifikasi webhook callback dari Xendit'),

                Toggle::make('enabled')
                    ->label('Aktifkan Xendit')
                    ->helperText('Jika diaktifkan, pembayaran via Xendit akan tersedia di POS'),

                Toggle::make('is_production')
                    ->label('Mode Production')
                    ->helperText('Aktifkan jika menggunakan API key production'),

                TextInput::make('invoice_duration')
                    ->label('Durasi Invoice (detik)')
                    ->numeric()
                    ->default(3600)
                    ->helperText('Waktu invoice valid sebelum expired (3600 = 1 jam)'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status Koneksi')
                    ->description('Status koneksi ke Xendit API')
                    ->schema([
                        Placeholder::make('connection_status')
                            ->label('Status')
                            ->content(fn () => $this->isConnected ? 'Terhubung' : 'Belum terhubung'),
                        Placeholder::make('environment')
                            ->label('Environment')
                            ->content(fn () => config('xendit.is_production') ? 'Production' : 'Development/Sandbox')
                            ->visible(fn () => $this->isConnected),
                        Placeholder::make('balance')
                            ->label('Saldo')
                            ->content(fn () => $this->connectionData ? 'Rp '.number_format($this->connectionData['balance'] ?? 0, 0, ',', '.') : '-')
                            ->visible(fn () => $this->isConnected && isset($this->connectionData['balance'])),
                    ])
                    ->columns(3),

                Section::make('Konfigurasi API')
                    ->description('Masukkan kredensial Xendit dari dashboard.xendit.co')
                    ->schema([
                        EmbeddedSchema::make('form'),
                    ])
                    ->columns(2),

                Section::make('Webhook URL')
                    ->description('Konfigurasi URL ini di Xendit Dashboard')
                    ->schema([
                        Placeholder::make('webhook_url')
                            ->label('Invoice Webhook URL')
                            ->content(fn () => url('/api/webhook/xendit/invoice'))
                            ->helperText('Copy URL ini ke Xendit Dashboard > Settings > Webhooks'),
                    ])
                    ->footerActions([
                        Action::make('save')
                            ->label('Simpan')
                            ->icon('heroicon-o-check')
                            ->color('success')
                            ->action('save'),
                        Action::make('testConnection')
                            ->label('Test Koneksi')
                            ->icon('heroicon-o-signal')
                            ->color('info')
                            ->action('testConnection'),
                    ])
                    ->footerActionsAlignment(Alignment::End),
            ]);
    }

    public function testConnection(): void
    {
        $secretKey = $this->data['secret_key'] ?? config('xendit.secret_key');

        if (empty($secretKey)) {
            Notification::make()
                ->title('Secret key tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        $xenditService = app(XenditService::class);
        $result = $xenditService->testConnection($secretKey);

        if ($result['success']) {
            $this->isConnected = true;
            $this->connectionMessage = $result['message'];
            $this->connectionData = $result['data'];

            Notification::make()
                ->title('Koneksi berhasil!')
                ->body('Environment: '.($result['data']['environment'] ?? 'unknown'))
                ->success()
                ->send();
        } else {
            $this->isConnected = false;
            $this->connectionMessage = $result['message'];
            $this->connectionData = null;

            Notification::make()
                ->title('Koneksi gagal')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }

    public function save(): void
    {
        try {
            $data = $this->form->getState();

            // Save to database instead of .env file
            Setting::set('xendit_enabled', $data['enabled'] ? 'true' : 'false', null, 'xendit');
            Setting::set('xendit_secret_key', $data['secret_key'], null, 'xendit');
            Setting::set('xendit_webhook_token', $data['webhook_token'], null, 'xendit');
            Setting::set('xendit_is_production', $data['is_production'] ? 'true' : 'false', null, 'xendit');
            Setting::set('xendit_invoice_duration', $data['invoice_duration'], null, 'xendit');

            Notification::make()
                ->title('Pengaturan berhasil disimpan')
                ->body('Konfigurasi Xendit telah diperbarui')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Log::error('XenditSettings save error: '.$e->getMessage());

            Notification::make()
                ->title('Gagal menyimpan')
                ->body('Terjadi kesalahan: '.$e->getMessage())
                ->danger()
                ->send();
        }
    }
}
