<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category.categoryType', 'baseUnit', 'activeBatches'])
            ->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('generic_name', 'like', "%{$search}%")
                    ->orWhere('kfa_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('requires_prescription')) {
            $query->requiresPrescription();
        }

        $products = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $products->through(fn ($product) => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'kfa_code' => $product->kfa_code,
                'name' => $product->name,
                'generic_name' => $product->generic_name,
                'image' => $product->image_url,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'category_type' => $product->category->categoryType ? [
                        'id' => $product->category->categoryType->id,
                        'name' => $product->category->categoryType->name,
                        'code' => $product->category->categoryType->code,
                        'color' => $product->category->categoryType->color,
                    ] : null,
                ] : null,
                'base_unit' => $product->baseUnit ? [
                    'id' => $product->baseUnit->id,
                    'name' => $product->baseUnit->name,
                ] : null,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'total_stock' => $product->total_stock,
                'min_stock' => $product->min_stock,
                'requires_prescription' => $product->requires_prescription,
                'is_low_stock' => $product->isLowStock(),
                'batches' => $product->activeBatches->map(fn ($batch) => [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expired_date' => $batch->expired_date->format('Y-m-d'),
                    'stock' => $batch->stock,
                    'selling_price' => $batch->selling_price,
                ]),
            ]),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load(['category.categoryType', 'baseUnit', 'activeBatches', 'unitConversions.toUnit']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'kfa_code' => $product->kfa_code,
                'name' => $product->name,
                'generic_name' => $product->generic_name,
                'description' => $product->description,
                'image' => $product->image_url,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'requires_prescription' => $product->category->requires_prescription,
                    'is_narcotic' => $product->category->is_narcotic,
                    'category_type' => $product->category->categoryType ? [
                        'id' => $product->category->categoryType->id,
                        'name' => $product->category->categoryType->name,
                        'code' => $product->category->categoryType->code,
                        'color' => $product->category->categoryType->color,
                        'requires_prescription' => $product->category->categoryType->requires_prescription,
                        'is_narcotic' => $product->category->categoryType->is_narcotic,
                    ] : null,
                ] : null,
                'base_unit' => $product->baseUnit ? [
                    'id' => $product->baseUnit->id,
                    'name' => $product->baseUnit->name,
                ] : null,
                'purchase_price' => $product->purchase_price,
                'selling_price' => $product->selling_price,
                'total_stock' => $product->total_stock,
                'min_stock' => $product->min_stock,
                'max_stock' => $product->max_stock,
                'rack_location' => $product->rack_location,
                'requires_prescription' => $product->requires_prescription,
                'is_active' => $product->is_active,
                'is_low_stock' => $product->isLowStock(),
                'batches' => $product->activeBatches->map(fn ($batch) => [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expired_date' => $batch->expired_date->format('Y-m-d'),
                    'stock' => $batch->stock,
                    'purchase_price' => $batch->purchase_price,
                    'selling_price' => $batch->selling_price,
                ]),
                'unit_conversions' => $product->unitConversions->map(fn ($conv) => [
                    'id' => $conv->id,
                    'unit' => $conv->toUnit ? [
                        'id' => $conv->toUnit->id,
                        'name' => $conv->toUnit->name,
                    ] : null,
                    'conversion_value' => $conv->conversion_value,
                    'selling_price' => $conv->selling_price ?? null,
                ]),
            ],
        ]);
    }

    public function searchByBarcode(Request $request): JsonResponse
    {
        $request->validate([
            'barcode' => 'required|string',
        ]);

        $product = Product::with(['category.categoryType', 'baseUnit', 'activeBatches'])
            ->active()
            ->where('barcode', $request->barcode)
            ->first();

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'code' => $product->code,
                'barcode' => $product->barcode,
                'kfa_code' => $product->kfa_code,
                'name' => $product->name,
                'generic_name' => $product->generic_name,
                'image' => $product->image_url,
                'category' => $product->category ? [
                    'id' => $product->category->id,
                    'name' => $product->category->name,
                    'category_type' => $product->category->categoryType ? [
                        'id' => $product->category->categoryType->id,
                        'name' => $product->category->categoryType->name,
                        'code' => $product->category->categoryType->code,
                        'color' => $product->category->categoryType->color,
                    ] : null,
                ] : null,
                'base_unit' => $product->baseUnit ? [
                    'id' => $product->baseUnit->id,
                    'name' => $product->baseUnit->name,
                ] : null,
                'selling_price' => $product->selling_price,
                'total_stock' => $product->total_stock,
                'requires_prescription' => $product->requires_prescription,
                'batches' => $product->activeBatches->map(fn ($batch) => [
                    'id' => $batch->id,
                    'batch_number' => $batch->batch_number,
                    'expired_date' => $batch->expired_date->format('Y-m-d'),
                    'stock' => $batch->stock,
                    'selling_price' => $batch->selling_price,
                ]),
            ],
        ]);
    }
}
