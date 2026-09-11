<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return 'Grafik Penjualan 7 Hari Terakhir';
    }

    protected function getData(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $end = now()->endOfDay();

        $salesByDate = Sale::query()
            ->selectRaw('DATE(date) as sale_date, SUM(total) as total')
            ->whereBetween('date', [$start, $end])
            ->groupBy('sale_date')
            ->pluck('total', 'sale_date');

        $data = [];
        $labels = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('D, d M');
            $data[] = (float) ($salesByDate[$date->format('Y-m-d')] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan (Rp)',
                    'data' => $data,
                    'fill' => true,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
