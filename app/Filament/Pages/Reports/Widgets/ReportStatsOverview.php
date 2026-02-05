<?php

namespace App\Filament\Pages\Reports\Widgets;

use App\Enums\SaleStatus;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;
use Livewire\Attributes\Reactive;

class ReportStatsOverview extends StatsOverviewWidget
{
    #[Reactive]
    public ?string $startDate = null;

    #[Reactive]
    public ?string $endDate = null;

    protected function getStats(): array
    {
        $startDate = $this->startDate ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $this->endDate ?? now()->format('Y-m-d');

        // Sales summary
        $sales = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', SaleStatus::Completed);

        $totalSales = (clone $sales)->sum('total');
        $totalTransactions = (clone $sales)->count();
        $avgTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;

        // Purchase summary
        $purchases = Purchase::whereBetween('date', [$startDate, $endDate]);
        $totalPurchases = (clone $purchases)->sum('total');
        $totalPurchaseCount = (clone $purchases)->count();

        // Gross profit estimation
        $grossProfit = SaleItem::whereHas('sale', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('date', [$startDate, $endDate])
                ->where('status', SaleStatus::Completed);
        })
            ->join('product_batches', 'sale_items.product_batch_id', '=', 'product_batches.id')
            ->selectRaw('SUM(sale_items.subtotal) - SUM(sale_items.quantity * product_batches.purchase_price) as profit')
            ->value('profit') ?? 0;

        $margin = $totalSales > 0 ? round(($grossProfit / $totalSales) * 100, 1) : 0;

        // Stock summary
        $totalStockValue = ProductBatch::where('stock', '>', 0)
            ->selectRaw('SUM(stock * purchase_price) as total')
            ->value('total') ?? 0;

        $lowStockCount = ProductBatch::where('stock', '>', 0)
            ->where('stock', '<', 10)
            ->count();

        $expiringCount = ProductBatch::where('stock', '>', 0)
            ->where('expired_date', '<=', now()->addDays(30))
            ->where('expired_date', '>', now())
            ->count();

        return [
            Stat::make('Total Penjualan', 'Rp '.Number::format($totalSales, 0, null, 'id'))
                ->description($totalTransactions.' transaksi | Avg: Rp '.Number::format($avgTransaction, 0, null, 'id'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Total Pembelian', 'Rp '.Number::format($totalPurchases, 0, null, 'id'))
                ->description($totalPurchaseCount.' purchase order')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Laba Kotor', 'Rp '.Number::format($grossProfit, 0, null, 'id'))
                ->description('Margin: '.$margin.'%')
                ->descriptionIcon($grossProfit >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($grossProfit >= 0 ? 'success' : 'danger'),

            Stat::make('Nilai Stok', 'Rp '.Number::format($totalStockValue, 0, null, 'id'))
                ->description(($lowStockCount > 0 ? $lowStockCount.' stok menipis' : 'Stok aman').($expiringCount > 0 ? ' | '.$expiringCount.' kadaluarsa' : ''))
                ->descriptionIcon($lowStockCount > 0 || $expiringCount > 0 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($lowStockCount > 0 || $expiringCount > 0 ? 'warning' : 'success'),
        ];
    }
}
