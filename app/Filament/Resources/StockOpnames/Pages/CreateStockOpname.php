<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Resources\StockOpnames\StockOpnameResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Arr;

class CreateStockOpname extends CreateRecord
{
    protected static string $resource = StockOpnameResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * The product/rack filters only change what's visible in the repeater. A relationship
     * Repeater persists directly from its own live state (before mutateFormDataBeforeCreate
     * ever runs), so the full item list must be restored here, right before validation/save.
     */
    public function create(bool $another = false): void
    {
        $this->restoreFullItemsFromSource();

        parent::create($another);
    }

    protected function restoreFullItemsFromSource(): void
    {
        $itemsSource = $this->data['items_source'] ?? null;

        if (is_array($itemsSource)) {
            $this->data['items'] = $itemsSource;
        }
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
            ->all();
    }

    /**
     * Merge the currently visible (possibly filtered) items back into the full item
     * source, so items hidden by the active filter are never lost when the visible
     * set is edited, added to, or has an item removed.
     *
     * @param  array<int|string, mixed>  $source
     * @param  array<int|string, mixed>  $visibleItems
     * @return array<int|string, mixed>
     */
    public static function mergeVisibleItemsIntoSource(array $source, array $visibleItems, int $productFilterId = 0, ?string $rackFilter = ''): array
    {
        $visibleKeys = array_keys(self::getFilteredItemsForStockOpname($source, $productFilterId, $rackFilter));

        return Arr::except($source, $visibleKeys) + $visibleItems;
    }
}
