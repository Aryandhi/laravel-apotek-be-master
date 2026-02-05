<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        return view('pos.products.index', compact('categories'));
    }

    public function search(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'baseUnit', 'activeBatches'])
            ->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('in_stock')) {
            $query->whereHas('activeBatches');
        }

        $products = $query->orderBy('name')->paginate(20);

        return response()->json([
            'data' => $products->map(fn ($product) => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'generic_name' => $product->generic_name,
                'category' => $product->category?->name,
                'unit' => $product->baseUnit?->name,
                'selling_price' => $product->selling_price,
                'total_stock' => $product->total_stock,
                'requires_prescription' => $product->requires_prescription,
                'is_low_stock' => $product->isLowStock(),
                'image_url' => $product->image_url,
            ]),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category', 'baseUnit', 'activeBatches', 'unitConversions.toUnit']);

        return response()->json([
            'id' => $product->id,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'generic_name' => $product->generic_name,
            'description' => $product->description,
            'category' => $product->category?->name,
            'unit' => $product->baseUnit?->name,
            'selling_price' => $product->selling_price,
            'total_stock' => $product->total_stock,
            'min_stock' => $product->min_stock,
            'rack_location' => $product->rack_location,
            'requires_prescription' => $product->requires_prescription,
            'is_low_stock' => $product->isLowStock(),
            'image_url' => $product->image_url,
            'batches' => $product->activeBatches->map(fn ($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expired_date' => $batch->expired_date->format('d M Y'),
                'days_until_expired' => $batch->daysUntilExpired(),
                'stock' => $batch->stock,
                'selling_price' => $batch->selling_price,
            ]),
            'unit_conversions' => $product->unitConversions->map(fn ($conv) => [
                'id' => $conv->id,
                'unit_name' => $conv->toUnit?->name,
                'conversion_value' => $conv->conversion_value,
                'selling_price' => $conv->selling_price,
            ]),
        ]);
    }

    public function searchBarcode(Request $request): JsonResponse
    {
        $request->validate(['barcode' => 'required|string']);

        $product = Product::with(['category', 'baseUnit', 'activeBatches'])
            ->active()
            ->where('barcode', $request->barcode)
            ->first();

        if (! $product) {
            return response()->json(['error' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $product->id,
            'code' => $product->code,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'generic_name' => $product->generic_name,
            'category' => $product->category?->name,
            'unit' => $product->baseUnit?->name,
            'selling_price' => $product->selling_price,
            'total_stock' => $product->total_stock,
            'requires_prescription' => $product->requires_prescription,
            'batches' => $product->activeBatches->map(fn ($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expired_date' => $batch->expired_date->format('d M Y'),
                'stock' => $batch->stock,
                'selling_price' => $batch->selling_price,
            ]),
        ]);
    }
}
