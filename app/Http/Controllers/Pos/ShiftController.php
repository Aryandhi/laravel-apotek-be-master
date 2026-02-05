<?php

namespace App\Http\Controllers\Pos;

use App\Enums\SaleStatus;
use App\Enums\ShiftStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CloseShiftRequest;
use App\Http\Requests\Pos\OpenShiftRequest;
use App\Models\CashierShift;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $currentShift = CashierShift::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Open)
            ->first();

        $recentShifts = CashierShift::query()
            ->where('user_id', $user->id)
            ->orderByDesc('opening_time')
            ->limit(10)
            ->get();

        return view('pos.shift.index', compact('currentShift', 'recentShifts'));
    }

    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        $existingShift = CashierShift::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Open)
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.shift.index')
                ->with('error', 'Anda sudah memiliki shift yang aktif');
        }

        return view('pos.shift.open');
    }

    public function store(OpenShiftRequest $request): RedirectResponse
    {
        $user = Auth::user();

        $existingShift = CashierShift::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Open)
            ->first();

        if ($existingShift) {
            return redirect()->route('pos.shift.index')
                ->with('error', 'Anda sudah memiliki shift yang aktif');
        }

        CashierShift::create([
            'user_id' => $user->id,
            'opening_cash' => $request->opening_cash,
            'expected_cash' => $request->opening_cash,
            'opening_time' => now(),
            'status' => ShiftStatus::Open,
            'notes' => $request->notes,
        ]);

        return redirect()->route('pos.dashboard')
            ->with('success', 'Shift berhasil dibuka');
    }

    public function showClose(): View|RedirectResponse
    {
        $user = Auth::user();
        $currentShift = CashierShift::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Open)
            ->first();

        if (! $currentShift) {
            return redirect()->route('pos.shift.index')
                ->with('error', 'Tidak ada shift yang aktif');
        }

        $expectedCash = $currentShift->calculateExpectedCash();
        $cashSalesTotal = $currentShift->getCashSalesTotal();

        // Basic sales summary
        $salesSummary = $currentShift->sales()
            ->where('status', SaleStatus::Completed)
            ->selectRaw('COUNT(*) as total_transactions, COALESCE(SUM(total), 0) as total_sales, COALESCE(SUM(discount), 0) as total_discount')
            ->first();

        // Payment method breakdown
        $paymentBreakdown = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
            ->where('sales.shift_id', $currentShift->id)
            ->where('sales.status', SaleStatus::Completed->value)
            ->select(
                'payment_methods.id',
                'payment_methods.name',
                'payment_methods.is_cash',
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                DB::raw('SUM(sale_payments.amount) as total_amount')
            )
            ->groupBy('payment_methods.id', 'payment_methods.name', 'payment_methods.is_cash')
            ->orderBy('payment_methods.name')
            ->get();

        // Recent transactions in this shift
        $recentSales = $currentShift->sales()
            ->with(['customer', 'payments.paymentMethod'])
            ->where('status', SaleStatus::Completed)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Items sold summary (top products)
        $topProducts = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.shift_id', $currentShift->id)
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

        return view('pos.shift.close', compact(
            'currentShift',
            'expectedCash',
            'cashSalesTotal',
            'salesSummary',
            'paymentBreakdown',
            'recentSales',
            'topProducts'
        ));
    }

    public function report(CashierShift $shift): View
    {
        // Ensure user can only see their own shift
        if ($shift->user_id !== Auth::id()) {
            abort(403);
        }

        // Basic sales summary
        $salesSummary = $shift->sales()
            ->where('status', SaleStatus::Completed)
            ->selectRaw('COUNT(*) as total_transactions, COALESCE(SUM(total), 0) as total_sales, COALESCE(SUM(discount), 0) as total_discount')
            ->first();

        // Payment method breakdown
        $paymentBreakdown = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
            ->where('sales.shift_id', $shift->id)
            ->where('sales.status', SaleStatus::Completed->value)
            ->select(
                'payment_methods.name',
                'payment_methods.is_cash',
                DB::raw('COUNT(DISTINCT sales.id) as transaction_count'),
                DB::raw('SUM(sale_payments.amount) as total_amount')
            )
            ->groupBy('payment_methods.id', 'payment_methods.name', 'payment_methods.is_cash')
            ->orderBy('payment_methods.name')
            ->get();

        // All transactions
        $sales = $shift->sales()
            ->with(['customer', 'payments.paymentMethod', 'items.product'])
            ->where('status', SaleStatus::Completed)
            ->orderBy('created_at')
            ->get();

        // Items sold
        $itemsSold = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.shift_id', $shift->id)
            ->where('sales.status', SaleStatus::Completed->value)
            ->select(
                'products.name',
                'products.code',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_sales')
            )
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderBy('products.name')
            ->get();

        $store = \App\Models\Store::first();

        return view('pos.shift.report', compact(
            'shift',
            'salesSummary',
            'paymentBreakdown',
            'sales',
            'itemsSold',
            'store'
        ));
    }

    public function close(CloseShiftRequest $request): RedirectResponse
    {
        $user = Auth::user();
        $currentShift = CashierShift::query()
            ->where('user_id', $user->id)
            ->where('status', ShiftStatus::Open)
            ->first();

        if (! $currentShift) {
            return redirect()->route('pos.shift.index')
                ->with('error', 'Tidak ada shift yang aktif');
        }

        $expectedCash = $currentShift->calculateExpectedCash();

        $currentShift->update([
            'actual_cash' => $request->actual_cash,
            'expected_cash' => $expectedCash,
            'difference' => $request->actual_cash - $expectedCash,
            'closing_time' => now(),
            'status' => ShiftStatus::Closed,
            'notes' => $request->notes ?? $currentShift->notes,
        ]);

        return redirect()->route('pos.shift.index')
            ->with('success', 'Shift berhasil ditutup');
    }
}
