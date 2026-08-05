<?php

namespace App\Filament\Widgets;

use App\Models\ProductBatch;
use App\Models\Setting;
use Filament\Widgets\Widget;

class InventoryAlerts extends Widget
{
    protected string $view = 'filament.widgets.inventory-alerts';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = ['md' => 1, 'xl' => 1];

    protected function getViewData(): array
    {
        $today = now()->startOfDay();
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', 10);

        $lowStockProductsCount = ProductBatch::query()
            ->where('stock', '>', 0)
            ->where('stock', '<', $lowStockThreshold)
            ->whereHas('product', function ($query): void {
                $query->where('is_active', true);
            })
            ->count();

        $lowStockItems = ProductBatch::query()
            ->with('product:id,name,min_stock')
            ->where('stock', '>', 0)
            ->where('stock', '<', $lowStockThreshold)
            ->whereHas('product', function ($query): void {
                $query->where('is_active', true);
            })
            ->orderBy('expired_date')
            ->limit(8)
            ->get();

        return [
            'lowStockThreshold' => $lowStockThreshold,
            'lowStockProductsCount' => $lowStockProductsCount,
            'lowStockItems' => $lowStockItems,
        ];
    }
}
