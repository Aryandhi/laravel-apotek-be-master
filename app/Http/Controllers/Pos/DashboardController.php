<?php

namespace App\Http\Controllers\Pos;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->startOfDay();
        $expiringLimit = $today->copy()->addDays(30);
        $lowStockThreshold = (int) Setting::get('low_stock_threshold', 10);

        $user = Auth::user();

        $currentShift = CashierShift::query()
            ->where('user_id', $user->id)
            ->where('status', 'open')
            ->first();

        // Today's sales summary
        $todaySales = Sale::query()
            ->whereDate('date', today())
            ->where('status', SaleStatus::Completed)
            ->selectRaw('COUNT(*) as total_transactions, COALESCE(SUM(total), 0) as total_revenue')
            ->first();

        // Payment breakdown for today
        $todayPayments = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
            ->whereDate('sales.date', today())
            ->where('sales.status', SaleStatus::Completed->value)
            ->select(
                'payment_methods.name',
                'payment_methods.is_cash',
                DB::raw('SUM(sale_payments.amount) as total_amount')
            )
            ->groupBy('payment_methods.id', 'payment_methods.name', 'payment_methods.is_cash')
            ->orderByDesc('total_amount')
            ->get();

        $todayPaymentTotal = (float) $todayPayments->sum('total_amount');

        // Recent transactions
        $recentSales = Sale::query()
            ->with(['customer', 'payments.paymentMethod'])
            ->where('status', SaleStatus::Completed)
            ->whereDate('date', today())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $lowStockProducts = ProductBatch::query()
            ->where('stock', '>', 0)
            ->where('stock', '<', $lowStockThreshold)
            ->whereHas('product', function ($query): void {
                $query->where('is_active', true);
            })
            ->count();

        $lowStockItems = ProductBatch::query()
            ->with(['product:id,name,min_stock'])
            ->where('stock', '>', 0)
            ->where('stock', '<', $lowStockThreshold)
            ->whereHas('product', function ($query): void {
                $query->where('is_active', true);
            })
            ->orderBy('expired_date')
            ->limit(20)
            ->get();

        $expiringBatches = ProductBatch::query()
            ->where('stock', '>', 0)
            ->whereBetween('expired_date', [$today, $expiringLimit])
            ->count();

        $expiringBatchItems = ProductBatch::query()
            ->with('product:id,name')
            ->where('stock', '>', 0)
            ->whereBetween('expired_date', [$today, $expiringLimit])
            ->orderBy('expired_date')
            ->limit(20)
            ->get();

        return view('pos.dashboard', compact(
            'currentShift',
            'todaySales',
            'todayPayments',
            'todayPaymentTotal',
            'lowStockThreshold',
            'recentSales',
            'lowStockProducts',
            'lowStockItems',
            'expiringBatches',
            'expiringBatchItems'
        ));
    }
}
