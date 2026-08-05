<?php

namespace App\Filament\Pages\Reports;

use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

abstract class BaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    public static function canAccess(): bool
    {
        $user = Auth::check() ? Auth::user() : null;

        return $user instanceof \Illuminate\Contracts\Auth\Access\Authorizable && $user->can('reports.view');
    }

    public ?string $startDate = null;

    public ?string $endDate = null;

    abstract protected function getReportTitle(): string;

    abstract protected function getReportQuery(): Builder;

    abstract protected function getReportColumns(): array;

    abstract protected function getExportHeadings(): array;

    abstract protected function getExportRow($record): array;

    protected function usesDateRangeFilter(): bool
    {
        return true;
    }

    public function mount(): void
    {
        if ($this->usesDateRangeFilter()) {
            $this->startDate = now()->startOfMonth()->format('Y-m-d');
            $this->endDate = now()->format('Y-m-d');

            return;
        }

        $this->startDate = null;
        $this->endDate = null;
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getReportTitle();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(fn () => $this->exportExcel()),

            Action::make('exportPdf')
                ->label('PDF')
                ->icon('heroicon-o-document')
                ->color('danger')
                ->action(fn () => $this->exportPdf()),

            Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (): string => $this->getPreviewPageUrl())
                ->openUrlInNewTab(),
        ];
    }

    protected function getAdditionalFilters(): array
    {
        return [];
    }

    public function content(Schema $schema): Schema
    {
        $filters = [];

        if ($this->usesDateRangeFilter()) {
            $filters[] = DatePicker::make('startDate')
                ->label('Dari Tanggal')
                ->default(now()->startOfMonth())
                ->native(false)
                ->displayFormat('d M Y')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable());

            $filters[] = DatePicker::make('endDate')
                ->label('Sampai Tanggal')
                ->default(now())
                ->native(false)
                ->displayFormat('d M Y')
                ->live()
                ->afterStateUpdated(fn () => $this->resetTable());
        }

        return $schema
            ->components([
                Section::make('Filter')
                    ->schema([
                        Grid::make()
                            ->schema([
                                ...$filters,
                                ...$this->getAdditionalFilters(),
                            ])
                            ->columns([
                                'default' => 2,
                                'sm' => 2,
                                'md' => 4,
                                'lg' => 4,
                                'xl' => 4,
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Data '.$this->getReportTitle())
                    ->schema([
                        EmbeddedTable::make(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getReportQuery())
            ->columns($this->getReportColumns())
            ->defaultSort($this->getDefaultSort(), $this->getDefaultSortDirection())
            ->defaultKeySort(false)
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public function getTableRecordKey($record): string
    {
        $keyName = $this->getTableRecordKeyName();

        if (is_array($record)) {
            return (string) ($record[$keyName] ?? uniqid());
        }

        return (string) ($record->{$keyName} ?? $record->getKey() ?? uniqid());
    }

    protected function getTableRecordKeyName(): string
    {
        return 'id';
    }

    protected function getDefaultSort(): string
    {
        return 'created_at';
    }

    protected function getDefaultSortDirection(): string
    {
        return 'desc';
    }

    public function exportExcel()
    {
        $filename = $this->getExportFilename('xlsx');

        return Excel::download(
            new \App\Exports\ReportExport(
                $this->getExportData(),
                $this->getExportHeadings()
            ),
            $filename
        );
    }

    public function exportPdf()
    {
        $filename = $this->getExportFilename('pdf');

        $period = $this->startDate && $this->endDate
            ? $this->startDate.' - '.$this->endDate
            : 'Semua data';

        $pdf = Pdf::loadView('exports.report-pdf', [
            'title' => $this->getReportTitle(),
            'period' => $period,
            'headings' => $this->getExportHeadings(),
            'data' => $this->getExportData(),
            'summary' => $this->getSummaryData(),
        ]);

        $pdf->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    public function printReport()
    {
        return redirect()->to($this->getPreviewPageUrl());
    }

    public function getPreviewPageUrl(): string
    {
        return route('reports.preview.page', array_merge(
            ['report' => $this->getPreviewReportKey()],
            $this->getPreviewQueryParameters()
        ));
    }

    public function getPreviewPdfUrl(bool $download = false): string
    {
        return route('reports.preview.pdf', array_merge(
            ['report' => $this->getPreviewReportKey(), 'download' => $download ? '1' : '0'],
            $this->getPreviewQueryParameters()
        ));
    }

    public function applyPreviewFilters(array $queryParameters): void
    {
        foreach ($this->getPreviewFilterMap() as $queryKey => $propertyName) {
            if (! array_key_exists($queryKey, $queryParameters)) {
                continue;
            }

            $value = $queryParameters[$queryKey];

            $this->{$propertyName} = is_scalar($value) ? (string) $value : null;
        }
    }

    public function getPreviewPayload(): array
    {
        return [
            'title' => $this->getReportTitle(),
            'period' => $this->startDate && $this->endDate
                ? $this->startDate.' - '.$this->endDate
                : 'Semua data',
            'headings' => $this->getExportHeadings(),
            'data' => $this->getExportData(),
            'summary' => $this->getSummaryData(),
            'storeName' => config('app.name'),
            'printDate' => now()->format('d/m/Y H:i'),
        ];
    }

    public function getPrintViewName(): string
    {
        return $this->getPrintView();
    }

    public function getPrintPaperSize(): array|string
    {
        return $this->getPrintPaperSizeConfig();
    }

    public function getPrintPaperOrientation(): string
    {
        return $this->getPrintPaperOrientationConfig();
    }

    public function getPrintPdfFilename(): string
    {
        return $this->getExportFilename('pdf');
    }

    public function getPreviewFilterMap(): array
    {
        $filters = $this->getAdditionalPreviewFilterMap();

        if ($this->usesDateRangeFilter()) {
            return array_merge([
                'start_date' => 'startDate',
                'end_date' => 'endDate',
            ], $filters);
        }

        return $filters;
    }

    protected function getAdditionalPreviewFilterMap(): array
    {
        return [];
    }

    protected function getPreviewQueryParameters(): array
    {
        $parameters = [];

        foreach ($this->getPreviewFilterMap() as $queryKey => $propertyName) {
            $value = $this->{$propertyName} ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $parameters[$queryKey] = (string) $value;
        }

        return $parameters;
    }

    protected function getPreviewReportKey(): string
    {
        return (string) str(class_basename(static::class))
            ->beforeLast('Report')
            ->kebab();
    }

    protected function getPrintView(): string
    {
        return 'exports.report-print';
    }

    protected function getPrintPaperSizeConfig(): array|string
    {
        return [0, 0, 612, 936];
    }

    protected function getPrintPaperOrientationConfig(): string
    {
        return 'portrait';
    }

    protected function getExportFilename(string $extension): string
    {
        $slug = str($this->getReportTitle())->slug();

        if ($this->startDate && $this->endDate) {
            return "{$slug}-{$this->startDate}-{$this->endDate}.{$extension}";
        }

        return "{$slug}-".now()->format('Y-m-d').".{$extension}";
    }

    public function getExportData(): Collection
    {
        return $this->getReportQuery()
            ->get()
            ->map(fn ($record) => $this->getExportRow($record));
    }

    protected function getSummaryData(): array
    {
        return [];
    }

    protected function formatMoney($value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    protected function formatDate($date): string
    {
        return $date ? $date->format('d/m/Y') : '-';
    }

    protected function formatDateTime($date): string
    {
        return $date ? $date->format('d/m/Y H:i') : '-';
    }
}
