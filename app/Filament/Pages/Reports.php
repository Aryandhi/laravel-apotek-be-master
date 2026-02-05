<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Reports\Widgets\ReportMenuWidget;
use App\Filament\Pages\Reports\Widgets\ReportStatsOverview;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Dashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Reports extends Dashboard
{
    use HasFiltersForm;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('reports.view') ?? false;
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?string $navigationLabel = 'Dashboard Laporan';

    protected static ?string $title = 'Dashboard Laporan';

    protected static UnitEnum|string|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $routePath = 'reports';

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->schema([
                        DatePicker::make('startDate')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->default(now()->startOfMonth()),
                        DatePicker::make('endDate')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d M Y')
                            ->default(now()),
                    ])
                    ->columns(4),
            ]);
    }

    public function getWidgets(): array
    {
        return [
            ReportStatsOverview::class,
            ReportMenuWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 1;
    }
}
