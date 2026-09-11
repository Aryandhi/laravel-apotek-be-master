<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $todayEnd = now()->endOfDay();
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Single aggregate query instead of 5 separate SUM/COUNT queries.
        $salesStats = Sale::query()->selectRaw(
            'COALESCE(SUM(CASE WHEN date >= ? AND date <= ? THEN total ELSE 0 END), 0) as today_sales,
             COUNT(CASE WHEN date >= ? AND date <= ? THEN 1 END) as today_transactions,
             COALESCE(SUM(CASE WHEN date >= ? THEN total ELSE 0 END), 0) as month_sales,
             COUNT(CASE WHEN date >= ? THEN 1 END) as month_transactions,
             COALESCE(SUM(CASE WHEN date >= ? AND date <= ? THEN total ELSE 0 END), 0) as last_month_sales',
            [$today, $todayEnd, $today, $todayEnd, $thisMonth, $thisMonth, $lastMonth, $lastMonthEnd]
        )->first();

        $todaySales = (float) $salesStats->today_sales;
        $todayTransactions = (int) $salesStats->today_transactions;
        $monthSales = (float) $salesStats->month_sales;
        $monthTransactions = (int) $salesStats->month_transactions;
        $lastMonthSales = (float) $salesStats->last_month_sales;

        // Persentase perubahan
        $salesChange = $lastMonthSales > 0
            ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        // Produk
        $totalProducts = Product::where('is_active', true)->count();

        return [
            Stat::make('Penjualan Hari Ini', 'Rp '.Number::format($todaySales, 0, null, 'id'))
                ->description($todayTransactions.' transaksi')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Penjualan Bulan Ini', 'Rp '.Number::format($monthSales, 0, null, 'id'))
                ->description($salesChange >= 0 ? '+'.$salesChange.'% dari bulan lalu' : $salesChange.'% dari bulan lalu')
                ->descriptionIcon($salesChange >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($salesChange >= 0 ? 'success' : 'danger'),

            Stat::make('Total Produk Aktif', Number::format($totalProducts, 0))
                ->description('Produk tersedia')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),

            Stat::make('Transaksi Bulan Ini', Number::format($monthTransactions, 0))
                ->description('Total transaksi')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),
        ];
    }
}
