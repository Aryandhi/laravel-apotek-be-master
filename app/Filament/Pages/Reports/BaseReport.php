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
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

abstract class BaseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    public static function canAccess(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    public ?string $startDate = null;

    public ?string $endDate = null;

    abstract protected function getReportTitle(): string;

    abstract protected function getReportQuery(): Builder;

    abstract protected function getReportColumns(): array;

    abstract protected function getExportHeadings(): array;

    abstract protected function getExportRow($record): array;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
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
                ->action(fn () => $this->printReport()),
        ];
    }

    protected function getAdditionalFilters(): array
    {
        return [];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Filter')
                    ->schema([
                        Grid::make()
                            ->schema([
                                DatePicker::make('startDate')
                                    ->label('Dari Tanggal')
                                    ->default(now()->startOfMonth())
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),
                                DatePicker::make('endDate')
                                    ->label('Sampai Tanggal')
                                    ->default(now())
                                    ->native(false)
                                    ->displayFormat('d M Y')
                                    ->live()
                                    ->afterStateUpdated(fn () => $this->resetTable()),
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

        $pdf = Pdf::loadView('exports.report-pdf', [
            'title' => $this->getReportTitle(),
            'period' => $this->startDate.' - '.$this->endDate,
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
        $filename = $this->getExportFilename('pdf');

        $pdf = Pdf::loadView('exports.report-print', [
            'title' => $this->getReportTitle(),
            'period' => $this->startDate.' - '.$this->endDate,
            'headings' => $this->getExportHeadings(),
            'data' => $this->getExportData(),
            'summary' => $this->getSummaryData(),
            'storeName' => config('app.name'),
            'printDate' => now()->format('d/m/Y H:i'),
        ]);

        // Format untuk dot matrix (continuous paper)
        $pdf->setPaper([0, 0, 612, 936], 'portrait'); // 8.5 x 13 inch

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    protected function getExportFilename(string $extension): string
    {
        $slug = str($this->getReportTitle())->slug();

        return "{$slug}-{$this->startDate}-{$this->endDate}.{$extension}";
    }

    protected function getExportData(): Collection
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
