<?php

namespace App\Http\Controllers\Pos;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
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

        // Current shift sales (if shift is open)
        $shiftSales = null;
        if ($currentShift) {
            $shiftSales = Sale::query()
                ->where('shift_id', $currentShift->id)
                ->where('status', SaleStatus::Completed)
                ->selectRaw('COUNT(*) as total_transactions, COALESCE(SUM(total), 0) as total_revenue')
                ->first();
        }

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

        // Top selling products today
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereDate('sales.date', today())
            ->where('sales.status', SaleStatus::Completed->value)
            ->select(
                'products.name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Recent transactions
        $recentSales = Sale::query()
            ->with(['customer', 'payments.paymentMethod'])
            ->where('status', SaleStatus::Completed)
            ->whereDate('date', today())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::query()
            ->where('is_active', true)
            ->whereRaw('(SELECT COALESCE(SUM(stock), 0) FROM product_batches WHERE product_batches.product_id = products.id AND product_batches.stock > 0) <= products.min_stock')
            ->count();

        $expiringBatches = ProductBatch::query()
            ->where('stock', '>', 0)
            ->whereBetween('expired_date', [now(), now()->addDays(30)])
            ->count();

        return view('pos.dashboard', compact(
            'currentShift',
            'todaySales',
            'shiftSales',
            'todayPayments',
            'topProducts',
            'recentSales',
            'lowStockProducts',
            'expiringBatches'
        ));
    }
}
