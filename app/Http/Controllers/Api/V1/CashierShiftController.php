<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierShiftController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        $shift = $request->user()->activeShift;

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'No active shift found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $shift->id,
                'opening_cash' => $shift->opening_cash,
                'expected_cash' => $shift->expected_cash,
                'opening_time' => $shift->opening_time->toIso8601String(),
                'status' => $shift->status->value,
            ],
        ]);
    }

    public function open(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active shift',
            ], 422);
        }

        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift = CashierShift::create([
            'user_id' => $user->id,
            'opening_cash' => $validated['opening_cash'],
            'expected_cash' => $validated['opening_cash'],
            'opening_time' => now(),
            'status' => 'open',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift opened successfully',
            'data' => [
                'id' => $shift->id,
                'opening_cash' => $shift->opening_cash,
                'opening_time' => $shift->opening_time->toIso8601String(),
            ],
        ], 201);
    }

    public function close(Request $request): JsonResponse
    {
        $user = $request->user();
        $shift = $user->activeShift;

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'No active shift found',
            ], 404);
        }

        $validated = $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $shift->update([
            'actual_cash' => $validated['actual_cash'],
            'difference' => $validated['actual_cash'] - $shift->expected_cash,
            'closing_time' => now(),
            'status' => 'closed',
            'notes' => $validated['notes'] ?? $shift->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift closed successfully',
            'data' => [
                'id' => $shift->id,
                'opening_cash' => $shift->opening_cash,
                'expected_cash' => $shift->expected_cash,
                'actual_cash' => $shift->actual_cash,
                'difference' => $shift->difference,
                'opening_time' => $shift->opening_time->toIso8601String(),
                'closing_time' => $shift->closing_time->toIso8601String(),
            ],
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $shift = $request->user()->activeShift;

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'No active shift found',
            ], 404);
        }

        // Get sales summary for this shift
        $salesQuery = Sale::where('shift_id', $shift->id);

        $totalSales = (clone $salesQuery)
            ->where('status', SaleStatus::Completed)
            ->sum('total');

        $totalTransactions = (clone $salesQuery)
            ->where('status', SaleStatus::Completed)
            ->count();

        $cancelledCount = (clone $salesQuery)
            ->where('status', SaleStatus::Cancelled)
            ->count();

        // Payment method breakdown
        $paymentBreakdown = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
            ->where('sales.shift_id', $shift->id)
            ->where('sales.status', SaleStatus::Completed->value)
            ->select(
                'payment_methods.name',
                'payment_methods.is_cash',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(sale_payments.amount) as total')
            )
            ->groupBy('payment_methods.id', 'payment_methods.name', 'payment_methods.is_cash')
            ->get();

        $cashTotal = $paymentBreakdown->where('is_cash', true)->sum('total');
        $nonCashTotal = $paymentBreakdown->where('is_cash', false)->sum('total');

        return response()->json([
            'success' => true,
            'data' => [
                'shift' => [
                    'id' => $shift->id,
                    'opening_cash' => (float) $shift->opening_cash,
                    'expected_cash' => (float) $shift->expected_cash,
                    'opening_time' => $shift->opening_time->toIso8601String(),
                    'duration' => $shift->opening_time->diffForHumans(now(), ['parts' => 2, 'short' => true]),
                ],
                'sales' => [
                    'total_transactions' => $totalTransactions,
                    'total_sales' => (float) $totalSales,
                    'cancelled_count' => $cancelledCount,
                    'average_transaction' => $totalTransactions > 0 ? round($totalSales / $totalTransactions, 2) : 0,
                ],
                'cash_flow' => [
                    'opening_cash' => (float) $shift->opening_cash,
                    'cash_sales' => (float) $cashTotal,
                    'non_cash_sales' => (float) $nonCashTotal,
                    'expected_cash' => (float) $shift->expected_cash,
                ],
                'payment_methods' => $paymentBreakdown->map(fn ($pm) => [
                    'name' => $pm->name,
                    'is_cash' => (bool) $pm->is_cash,
                    'count' => (int) $pm->count,
                    'total' => (float) $pm->total,
                ]),
            ],
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $shift = $request->user()->activeShift;

        if (! $shift) {
            return response()->json([
                'success' => false,
                'message' => 'No active shift found',
            ], 404);
        }

        $sales = Sale::where('shift_id', $shift->id)
            ->with(['customer', 'payments.paymentMethod'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $sales->through(fn ($sale) => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'customer' => $sale->customer?->name ?? 'Umum',
                'total' => (float) $sale->total,
                'status' => $sale->status->value,
                'payment_method' => $sale->payments->first()?->paymentMethod?->name ?? 'Cash',
                'time' => $sale->created_at->format('H:i'),
            ]),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);
    }
}
