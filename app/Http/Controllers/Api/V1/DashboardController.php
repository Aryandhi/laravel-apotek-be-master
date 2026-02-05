<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $today = today();

        $todaySales = Sale::whereDate('date', $today)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        $lowStockProducts = Product::active()
            ->get()
            ->filter(fn ($product) => $product->isLowStock())
            ->count();

        $expiringSoon = ProductBatch::whereIn('status', ['active', 'near_expired'])
            ->where('stock', '>', 0)
            ->whereBetween('expired_date', [$today, $today->copy()->addDays(30)])
            ->count();

        $expired = ProductBatch::whereIn('status', ['active', 'near_expired', 'expired'])
            ->where('stock', '>', 0)
            ->where('expired_date', '<', $today)
            ->count();

        $activeShift = $user->activeShift;

        return response()->json([
            'success' => true,
            'data' => [
                'today_sales' => [
                    'count' => $todaySales->count ?? 0,
                    'total' => $todaySales->total ?? 0,
                ],
                'low_stock_products' => $lowStockProducts,
                'expiring_soon' => $expiringSoon,
                'expired_products' => $expired,
                'active_shift' => $activeShift ? [
                    'id' => $activeShift->id,
                    'opening_cash' => $activeShift->opening_cash,
                    'expected_cash' => $activeShift->expected_cash,
                    'opening_time' => $activeShift->opening_time->toIso8601String(),
                ] : null,
            ],
        ]);
    }

    public function lowStockProducts(): JsonResponse
    {
        $products = Product::active()
            ->with(['category', 'baseUnit'])
            ->get()
            ->filter(fn ($product) => $product->isLowStock())
            ->take(20)
            ->values();

        return response()->json([
            'success' => true,
            'data' => $products->map(fn ($product) => [
                'id' => $product->id,
                'code' => $product->code,
                'name' => $product->name,
                'category' => $product->category?->name,
                'unit' => $product->baseUnit?->name,
                'total_stock' => $product->total_stock,
                'min_stock' => $product->min_stock,
            ]),
        ]);
    }

    public function expiringBatches(): JsonResponse
    {
        $today = today();

        $batches = ProductBatch::with(['product.baseUnit'])
            ->whereIn('status', ['active', 'near_expired', 'expired'])
            ->where('stock', '>', 0)
            ->where('expired_date', '<=', $today->copy()->addDays(30))
            ->orderBy('expired_date')
            ->take(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $batches->map(fn ($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'product' => [
                    'id' => $batch->product->id,
                    'name' => $batch->product->name,
                    'code' => $batch->product->code,
                ],
                'unit' => $batch->product->baseUnit?->name,
                'stock' => $batch->stock,
                'expired_date' => $batch->expired_date->format('Y-m-d'),
                'days_until_expiry' => $batch->expired_date->diffInDays($today, false),
                'is_expired' => $batch->expired_date->isPast(),
            ]),
        ]);
    }
}
