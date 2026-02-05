<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function salesSummary(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Total sales summary
        $summary = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_transactions,
                COALESCE(SUM(total), 0) as total_sales,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(AVG(total), 0) as average_transaction
            ')
            ->first();

        // Daily breakdown
        $dailySales = Sale::whereBetween('date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('DATE(date) as date, COUNT(*) as transactions, SUM(total) as total')
            ->groupBy(DB::raw('DATE(date)'))
            ->orderBy('date')
            ->get();

        // Top selling products
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->selectRaw('
                products.id,
                products.name,
                products.code,
                SUM(sale_items.quantity) as total_qty,
                SUM(sale_items.subtotal) as total_sales
            ')
            ->groupBy('products.id', 'products.name', 'products.code')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Payment method breakdown
        $paymentMethods = DB::table('sale_payments')
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id')
            ->whereBetween('sales.date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->selectRaw('
                payment_methods.name,
                COUNT(*) as count,
                SUM(sale_payments.amount) as total
            ')
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->orderByDesc('total')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'summary' => [
                    'total_transactions' => (int) $summary->total_transactions,
                    'total_sales' => (float) $summary->total_sales,
                    'total_discount' => (float) $summary->total_discount,
                    'average_transaction' => (float) $summary->average_transaction,
                ],
                'daily_sales' => $dailySales->map(fn ($day) => [
                    'date' => $day->date,
                    'transactions' => (int) $day->transactions,
                    'total' => (float) $day->total,
                ]),
                'top_products' => $topProducts->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'code' => $product->code,
                    'total_qty' => (int) $product->total_qty,
                    'total_sales' => (float) $product->total_sales,
                ]),
                'payment_methods' => $paymentMethods->map(fn ($pm) => [
                    'name' => $pm->name,
                    'count' => (int) $pm->count,
                    'total' => (float) $pm->total,
                ]),
            ],
        ]);
    }
}
