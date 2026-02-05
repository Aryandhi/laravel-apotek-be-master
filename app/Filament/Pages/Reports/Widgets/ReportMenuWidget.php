<?php

namespace App\Filament\Pages\Reports\Widgets;

use Filament\Widgets\Widget;

class ReportMenuWidget extends Widget
{
    protected string $view = 'filament.pages.reports.widgets.report-menu';

    protected int|string|array $columnSpan = 'full';
}
