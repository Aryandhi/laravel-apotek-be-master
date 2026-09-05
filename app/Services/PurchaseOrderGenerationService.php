<?php

namespace App\Services;

use App\Enums\CategoryType as CategoryTypeEnum;
use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchasePlanItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Core "Rules Engine" for splitting Perencanaan (planning) selections into
 * Surat Pesanan (Purchase Order) documents, grouped by Supplier + Tipe Kategori,
 * per BPOM/Kemenkes regulation (see PRD_Surat_Pesanan.md).
 */
class PurchaseOrderGenerationService
{
    /**
     * Generate Surat Pesanan documents from all planning items that have a supplier assigned.
     *
     * @return Collection<int, PurchaseOrder>
     */
    public function generate(?int $userId = null): Collection
    {
        $planItems = PurchasePlanItem::query()
            ->whereNotNull('supplier_id')
            ->with(['product.category', 'product.categoryType', 'product.baseUnit'])
            ->get();

        if ($planItems->isEmpty()) {
            throw new RuntimeException('Tidak ada produk dengan supplier terpilih untuk dibuatkan Surat Pesanan.');
        }

        return DB::transaction(function () use ($planItems, $userId) {
            $createdOrders = new Collection;

            $grouped = $planItems->groupBy(
                fn (PurchasePlanItem $item) => $item->supplier_id.'|'.$this->resolveGroup($item->product)->value
            );

            foreach ($grouped as $items) {
                $group = $this->resolveGroup($items->first()->product);
                $supplierId = $items->first()->supplier_id;
                $maxItemsPerDocument = $group->maxItemsPerDocument();

                $chunks = $maxItemsPerDocument
                    ? $items->chunk($maxItemsPerDocument)
                    : new Collection([$items]);

                foreach ($chunks as $chunkItems) {
                    $order = PurchaseOrder::create([
                        'po_number' => $this->generatePoNumber($group),
                        'title' => $group->documentTitle(),
                        'group' => $group,
                        'supplier_id' => $supplierId,
                        'status' => PurchaseOrderStatus::Draft,
                        'order_date' => now()->toDateString(),
                        'user_id' => $userId,
                    ]);

                    foreach ($chunkItems as $planItem) {
                        $product = $planItem->product;

                        $order->items()->create([
                            'product_id' => $product->id,
                            'unit_id' => $product->base_unit_id,
                            'quantity' => $this->resolveOrderQuantity($product),
                        ]);
                    }

                    $createdOrders->push($order);
                }
            }

            PurchasePlanItem::query()->whereIn('id', $planItems->pluck('id'))->delete();

            return $createdOrders;
        });
    }

    public function resolveGroup(Product $product): PurchaseOrderGroup
    {
        $code = $product->categoryType?->code ?? $product->category?->type?->value;
        $type = $code ? CategoryTypeEnum::tryFrom($code) : null;

        return $type?->purchaseOrderGroup() ?? PurchaseOrderGroup::Reguler;
    }

    private function resolveOrderQuantity(Product $product): int
    {
        return max(1, $product->min_stock - $product->total_stock);
    }

    private function generatePoNumber(PurchaseOrderGroup $group): string
    {
        $prefix = $group->numberPrefix();
        $pattern = $prefix.'-'.now()->format('Y').'-';

        $lastNumber = PurchaseOrder::query()
            ->where('po_number', 'like', $pattern.'%')
            ->lockForUpdate()
            ->orderByDesc('po_number')
            ->value('po_number');

        $sequence = $lastNumber ? ((int) substr($lastNumber, -6)) + 1 : 1;

        return $pattern.str_pad((string) $sequence, 6, '0', STR_PAD_LEFT);
    }
}
