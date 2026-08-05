<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public static function getProgressSummary(array $items, array $productNamesById = []): array
    {
        $products = [];
        $totalItems = 0;
        $withDiscrepancy = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $productId = (int) ($item['product_id'] ?? 0);
            if (! $productId) {
                continue;
            }

            $totalItems++;

            if ((int) ($item['difference'] ?? 0) !== 0) {
                $withDiscrepancy++;
            }

            $productName = $productNamesById[$productId] ?? Product::find($productId)?->name ?? 'Produk tidak ditemukan';

            if (! isset($products[$productId])) {
                $products[$productId] = [
                    'id' => $productId,
                    'name' => $productName,
                    'count' => 0,
                    'with_discrepancy' => 0,
                ];
            }

            $products[$productId]['count']++;

            if ((int) ($item['difference'] ?? 0) !== 0) {
                $products[$productId]['with_discrepancy']++;
            }
        }

        return [
            'total_items' => $totalItems,
            'with_discrepancy' => $withDiscrepancy,
            'products' => $products,
        ];
    }

    public static function getAvailableProductOptionsForItems(array $items): array
    {
        $productIds = collect($items)
            ->filter(fn ($item) => is_array($item) && ! empty($item['product_id']))
            ->pluck('product_id')
            ->filter(fn ($productId) => (int) $productId > 0)
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    public static function getAvailableRackLocationOptionsForItems(array $items): array
    {
        $productIds = collect($items)
            ->filter(fn ($item) => is_array($item) && ! empty($item['product_id']))
            ->pluck('product_id')
            ->filter(fn ($productId) => (int) $productId > 0)
            ->unique()
            ->values()
            ->all();

        if ($productIds === []) {
            return [];
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->whereNotNull('rack_location')
            ->where('rack_location', '!=', '')
            ->distinct()
            ->orderBy('rack_location')
            ->pluck('rack_location', 'rack_location')
            ->toArray();
    }

    public static function getFilteredItemsForStockOpname(array $items, int $productFilterId = 0, ?string $rackFilter = ''): array
    {
        $rackFilter = $rackFilter ?? '';

        return collect($items)
            ->filter(function ($item) use ($productFilterId, $rackFilter): bool {
                if (! is_array($item)) {
                    return false;
                }

                if ($productFilterId > 0 && ((int) ($item['product_id'] ?? 0)) !== $productFilterId) {
                    return false;
                }

                if ($rackFilter !== '') {
                    $product = Product::find((int) ($item['product_id'] ?? 0));
                    if (! $product || $product->rack_location !== $rackFilter) {
                        return false;
                    }
                }

                return true;
            })
            ->values()
            ->all();
    }
}
