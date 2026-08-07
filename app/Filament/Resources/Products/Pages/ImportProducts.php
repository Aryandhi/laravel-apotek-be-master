<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Services\ProductImportService;
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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class ImportProducts extends Page
{
    protected static string $resource = ProductResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        return $user instanceof Authorizable && $user->can('products.create');
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $title = 'Import Produk';

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
                    ->label('File Excel Produk')
                    ->required()
                    ->directory('import-products')
                    ->disk('local')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                        'text/csv',
                    ])
                    ->helperText('Gunakan file Excel dengan kolom barcode, name, generic_name, category, unit, min_stock, max_stock, rack_location, description'),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Import Produk')
                    ->description('Unggah file Excel dan validasi data sebelum eksekusi import.')
                    ->schema([
                        EmbeddedSchema::make('form'),
                        Placeholder::make('validation_results')
                            ->label('Hasil Validasi')
                            ->content(fn (): string|Htmlable => $this->renderValidationResults()),
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

        \Log::debug('ImportProducts validateImport state', [
            'formState' => $formState,
            'publicData' => $this->data,
            'componentFileAttachments' => $this->componentFileAttachments ?? null,
        ]);

        $fileValue = $formState['file'] ?? $this->data['file'] ?? null;
        $filePath = $this->resolveUploadedFilePath($fileValue);

        if (! $filePath) {
            Notification::make()
                ->title('File belum dipilih')
                ->body('Silakan pilih file Excel yang berisi data produk sebelum melakukan validasi.')
                ->danger()
                ->send();

            return;
        }

        try {
            $importService = new ProductImportService;
            $fullPath = Storage::disk('local')->path($filePath);
            $result = $importService->validateFile($fullPath);
        } catch (\Exception $exception) {
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
                ->body('Terdapat kesalahan pada file import. Periksa detail berikut dan perbaiki isian file Anda.')
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
        if (empty($this->validatedRows)) {
            Notification::make()
                ->title('Tidak ada data yang tervalidasi')
                ->body('Silakan jalankan validasi terlebih dahulu sebelum mengeksekusi import.')
                ->danger()
                ->send();

            return;
        }

        try {
            $importService = new ProductImportService;
            $created = $importService->importRows($this->validatedRows);

            Notification::make()
                ->title('Import berhasil')
                ->body("{$created} produk berhasil disimpan ke database.")
                ->success()
                ->send();

            $this->redirect(ProductResource::getUrl('index'));
        } catch (\Exception $exception) {
            Notification::make()
                ->title('Gagal menyimpan produk')
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

    protected function renderValidationResults(): string|Htmlable
    {
        if ($this->validationErrors !== null && count($this->validationErrors) > 0) {
            $items = collect($this->validationErrors)
                ->map(fn (string $error): string => '<li>'.e($error).'</li>')
                ->implode('');

            return new HtmlString("<ul class=\"list-disc list-inside space-y-1 text-danger-600\">{$items}</ul>");
        }

        if ($this->validatedRowCount !== null) {
            return "{$this->validatedRowCount} baris berhasil tervalidasi dan siap untuk diimport.";
        }

        return 'Silakan unggah file Excel terlebih dahulu kemudian klik Validasi Upload.';
    }
}
