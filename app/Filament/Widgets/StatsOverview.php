<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\ProductBatch;
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
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Penjualan hari ini
        $todaySales = Sale::whereDate('date', $today)->sum('total');
        $todayTransactions = Sale::whereDate('date', $today)->count();

        // Penjualan bulan ini
        $monthSales = Sale::where('date', '>=', $thisMonth)->sum('total');
        $monthTransactions = Sale::where('date', '>=', $thisMonth)->count();

        // Penjualan bulan lalu (untuk perbandingan)
        $lastMonthSales = Sale::whereBetween('date', [$lastMonth, $lastMonthEnd])->sum('total');

        // Persentase perubahan
        $salesChange = $lastMonthSales > 0
            ? round((($monthSales - $lastMonthSales) / $lastMonthSales) * 100, 1)
            : 0;

        // Produk
        $totalProducts = Product::where('is_active', true)->count();

        // Stok menipis (< 10)
        $lowStock = ProductBatch::where('stock', '>', 0)
            ->where('stock', '<', 10)
            ->count();

        // Kadaluarsa dalam 30 hari
        $expiringSoon = ProductBatch::where('stock', '>', 0)
            ->where('expired_date', '<=', now()->addDays(30))
            ->where('expired_date', '>', now())
            ->count();

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

            Stat::make('Stok Menipis', Number::format($lowStock, 0))
                ->description('Perlu restok segera')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStock > 0 ? 'warning' : 'success'),

            Stat::make('Kadaluarsa < 30 Hari', Number::format($expiringSoon, 0))
                ->description('Segera jual/retur')
                ->descriptionIcon('heroicon-m-clock')
                ->color($expiringSoon > 0 ? 'danger' : 'success'),

            Stat::make('Transaksi Bulan Ini', Number::format($monthTransactions, 0))
                ->description('Total transaksi')
                ->descriptionIcon('heroicon-m-receipt-percent')
                ->color('primary'),
        ];
    }
}
