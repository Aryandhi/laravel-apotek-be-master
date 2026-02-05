<?php

namespace App\Filament\Resources\XenditTransactions\Widgets;

use App\Enums\XenditPaymentStatus;
use App\Models\XenditTransaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class XenditStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Total transaksi hari ini
        $todayTransactions = XenditTransaction::whereDate('created_at', $today)->count();
        $todayPaid = XenditTransaction::whereDate('created_at', $today)
            ->whereIn('status', [XenditPaymentStatus::Paid, XenditPaymentStatus::Settled])
            ->count();
        $todayAmount = XenditTransaction::whereDate('created_at', $today)
            ->whereIn('status', [XenditPaymentStatus::Paid, XenditPaymentStatus::Settled])
            ->sum('amount');

        // Total transaksi bulan ini
        $monthTransactions = XenditTransaction::where('created_at', '>=', $thisMonth)->count();
        $monthPaid = XenditTransaction::where('created_at', '>=', $thisMonth)
            ->whereIn('status', [XenditPaymentStatus::Paid, XenditPaymentStatus::Settled])
            ->count();
        $monthAmount = XenditTransaction::where('created_at', '>=', $thisMonth)
            ->whereIn('status', [XenditPaymentStatus::Paid, XenditPaymentStatus::Settled])
            ->sum('amount');

        // Pending transactions
        $pendingCount = XenditTransaction::where('status', XenditPaymentStatus::Pending)->count();
        $pendingAmount = XenditTransaction::where('status', XenditPaymentStatus::Pending)->sum('amount');

        // Success rate bulan ini
        $successRate = $monthTransactions > 0
            ? round(($monthPaid / $monthTransactions) * 100, 1)
            : 0;

        // Transaksi per metode (bulan ini)
        $byMethod = XenditTransaction::where('created_at', '>=', $thisMonth)
            ->whereIn('status', [XenditPaymentStatus::Paid, XenditPaymentStatus::Settled])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $qrisCount = $byMethod->get('QRIS')?->count ?? 0;
        $ewalletCount = $byMethod->get('EWALLET')?->count ?? 0;

        return [
            Stat::make('Transaksi Hari Ini', $todayPaid.'/'.$todayTransactions)
                ->description('Rp '.Number::format($todayAmount, 0, null, 'id'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Transaksi Bulan Ini', $monthPaid.'/'.$monthTransactions)
                ->description('Rp '.Number::format($monthAmount, 0, null, 'id'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('primary'),

            Stat::make('Success Rate', $successRate.'%')
                ->description($monthPaid.' berhasil dari '.$monthTransactions.' transaksi')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($successRate >= 80 ? 'success' : ($successRate >= 50 ? 'warning' : 'danger')),

            Stat::make('Menunggu Pembayaran', $pendingCount)
                ->description('Rp '.Number::format($pendingAmount, 0, null, 'id'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'gray'),

            Stat::make('QRIS', $qrisCount.' transaksi')
                ->description('Rp '.Number::format($byMethod->get('QRIS')?->total ?? 0, 0, null, 'id'))
                ->descriptionIcon('heroicon-m-qr-code')
                ->color('success'),

            Stat::make('E-Wallet', $ewalletCount.' transaksi')
                ->description('Rp '.Number::format($byMethod->get('EWALLET')?->total ?? 0, 0, null, 'id'))
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('info'),
        ];
    }
}
