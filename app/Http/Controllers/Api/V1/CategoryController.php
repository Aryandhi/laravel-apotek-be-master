<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CategoryType;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->with('categoryType')
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type?->value,
                'category_type' => $category->categoryType ? [
                    'id' => $category->categoryType->id,
                    'name' => $category->categoryType->name,
                    'code' => $category->categoryType->code,
                    'color' => $category->categoryType->color,
                    'requires_prescription' => $category->categoryType->requires_prescription,
                    'is_narcotic' => $category->categoryType->is_narcotic,
                ] : null,
                'requires_prescription' => $category->requires_prescription,
                'is_narcotic' => $category->is_narcotic,
                'products_count' => $category->products_count,
            ]),
        ]);
    }

    public function types(): JsonResponse
    {
        $types = CategoryType::query()
            ->active()
            ->ordered()
            ->withCount('categories')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $types->map(fn ($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'code' => $type->code,
                'description' => $type->description,
                'color' => $type->color,
                'requires_prescription' => $type->requires_prescription,
                'is_narcotic' => $type->is_narcotic,
                'categories_count' => $type->categories_count,
            ]),
        ]);
    }
}
