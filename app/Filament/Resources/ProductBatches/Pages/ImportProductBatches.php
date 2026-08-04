<?php

namespace App\Filament\Resources\ProductBatches\Pages;

use App\Filament\Resources\ProductBatches\ProductBatchResource;
use App\Services\ProductBatchImportService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ImportProductBatches extends Page
{
    protected static string $resource = ProductBatchResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        return $user instanceof Authorizable && $user->can('stock.create');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?string $title = 'Import Batch Produk';

    public array $data = [];

    public ?array $validatedRows = null;

    public ?array $validationErrors = null;

    public ?int $validatedRowCount = null;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                FileUpload::make('file')
                    ->label('File Excel Batch Produk')
                    ->required()
                    ->directory('import-product-batches')
                    ->disk('local')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->helperText('Gunakan file Excel dengan kolom product, batch_number, expired_date, purchase_price, margin_percentage, selling_price, stock, initial_stock, status'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Import Batch Produk')
                    ->description('Unggah file Excel lalu validasi sebelum eksekusi import batch.')
                    ->schema([
                        EmbeddedSchema::make('form'),
                        Placeholder::make('validation_results')
                            ->label('Hasil Validasi')
                            ->content(fn (): string => $this->renderValidationResults()),
                    ])
                    ->footerActions([
                        Action::make('validate')
                            ->label('Validasi Upload')
                            ->icon('heroicon-o-check-badge')
                            ->action('validateImport'),
                        Action::make('execute')
                            ->label('Execute Import')
                            ->icon('heroicon-o-cloud-arrow-up')
                            ->color('success')
                            ->requiresConfirmation()
                            ->visible(fn (): bool => $this->validatedRowCount !== null && $this->validatedRowCount > 0 && empty($this->validationErrors))
                            ->action('executeImport'),
                    ])
                    ->footerActionsAlignment(Alignment::End),
            ]);
    }

    public function validateImport(): void
    {
        $formState = null;

        if (isset($this->form)) {
            $formState = $this->form->getState();
        }

        \Log::debug('ImportProductBatches validateImport state', [
            'formState' => $formState,
            'publicData' => $this->data,
        ]);

        $fileValue = $formState['file'] ?? $this->data['file'] ?? null;
        $filePath = $this->resolveUploadedFilePath($fileValue);

        if (! $filePath) {
            Notification::make()
                ->title('File belum dipilih')
                ->body('Silakan pilih file Excel yang berisi data batch produk sebelum melakukan validasi.')
                ->danger()
                ->send();

            return;
        }

        try {
            $importService = new ProductBatchImportService();
            $fullPath = Storage::disk('local')->path($filePath);
            $result = $importService->validateFile($fullPath);
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gagal memproses file')
                ->body('Terjadi kesalahan saat membaca file: '.$exception->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->validatedRows = $result['rows'];
        $this->validationErrors = $result['errors'];
        $this->validatedRowCount = count($this->validatedRows);

        if (! empty($this->validationErrors)) {
            Notification::make()
                ->title('Validasi gagal')
                ->body('Terdapat masalah pada file import. Periksa detail berikut sebelum melanjutkan.')
                ->danger()
                ->send();

            return;
        }

        Notification::make()
            ->title('Validasi berhasil')
            ->body("{$this->validatedRowCount} baris valid. Klik Execute Import untuk menyimpan data.")
            ->success()
            ->send();
    }

    public function executeImport(): void
    {
        if (empty($this->validatedRows) || ! empty($this->validationErrors)) {
            Notification::make()
                ->title('Tidak ada data yang siap diimport')
                ->body('Silakan jalankan validasi terlebih dahulu dan pastikan tidak ada error sebelum mengeksekusi import.')
                ->danger()
                ->send();

            return;
        }

        try {
            $importService = new ProductBatchImportService();
            $created = $importService->importRows($this->validatedRows);

            Notification::make()
                ->title('Import berhasil')
                ->body("{$created} batch produk berhasil disimpan ke database.")
                ->success()
                ->send();

            $this->redirect(ProductBatchResource::getUrl('index'));
        } catch (\Throwable $exception) {
            Notification::make()
                ->title('Gagal menyimpan batch produk')
                ->body('Terjadi kesalahan saat menyimpan data: '.$exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function resolveUploadedFilePath(mixed $fileValue): ?string
    {
        if (is_string($fileValue)) {
            return $fileValue;
        }

        if (! is_array($fileValue)) {
            return null;
        }

        if (isset($fileValue['filePath'])) {
            return $fileValue['filePath'];
        }

        if (isset($fileValue['path'])) {
            return $fileValue['path'];
        }

        if (isset($fileValue[0]) && is_string($fileValue[0])) {
            return $fileValue[0];
        }

        $firstValue = reset($fileValue);

        if (is_string($firstValue)) {
            return $firstValue;
        }

        if (is_array($firstValue) && isset($firstValue['filePath'])) {
            return $firstValue['filePath'];
        }

        if (is_array($firstValue) && isset($firstValue['path'])) {
            return $firstValue['path'];
        }

        return null;
    }

    protected function renderValidationResults(): string
    {
        if ($this->validationErrors !== null && count($this->validationErrors) > 0) {
            return implode("\n", $this->validationErrors);
        }

        if ($this->validatedRowCount !== null) {
            return "{$this->validatedRowCount} baris berhasil tervalidasi dan siap untuk diimport.";
        }

        return 'Silakan unggah file Excel terlebih dahulu kemudian klik Validasi Upload.';
    }
}
