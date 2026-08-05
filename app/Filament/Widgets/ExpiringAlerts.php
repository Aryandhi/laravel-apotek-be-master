<?php

namespace App\Filament\Widgets;

use App\Models\ProductBatch;
use Filament\Widgets\Widget;

class ExpiringAlerts extends Widget
{
    protected string $view = 'filament.widgets.expiring-alerts';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getViewData(): array
    {
        $today = now()->startOfDay();
        $expiringLimit = $today->copy()->addDays(30);

        $expiringSoonCount = ProductBatch::query()
            ->where('stock', '>', 0)
            ->whereBetween('expired_date', [$today, $expiringLimit])
            ->count();

        $expiringSoonItems = ProductBatch::query()
            ->with('product:id,name')
            ->where('stock', '>', 0)
            ->whereBetween('expired_date', [$today, $expiringLimit])
            ->orderBy('expired_date')
            ->limit(8)
            ->get();

        return [
            'expiringSoonCount' => $expiringSoonCount,
            'expiringSoonItems' => $expiringSoonItems,
        ];
    }
}
