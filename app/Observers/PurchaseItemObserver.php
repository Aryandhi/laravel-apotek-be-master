<?php

namespace App\Observers;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseItem;
use App\Models\PurchaseOrderItem;

/**
 * Keeps Surat Pesanan (PurchaseOrder) item received quantities & status in sync
 * whenever an invoice item referencing it is created, edited or removed.
 */
class PurchaseItemObserver
{
    public function created(PurchaseItem $item): void
    {
        if ($item->purchase_order_item_id) {
            $this->syncPurchaseOrderItem((int) $item->purchase_order_item_id);
        }
    }

    public function updated(PurchaseItem $item): void
    {
        if (! $item->wasChanged('quantity') && ! $item->wasChanged('purchase_order_item_id')) {
            return;
        }

        if ($item->purchase_order_item_id) {
            $this->syncPurchaseOrderItem((int) $item->purchase_order_item_id);
        }

        $previousOrderItemId = $item->getOriginal('purchase_order_item_id');

        if ($previousOrderItemId && (int) $previousOrderItemId !== (int) $item->purchase_order_item_id) {
            $this->syncPurchaseOrderItem((int) $previousOrderItemId);
        }
    }

    public function deleted(PurchaseItem $item): void
    {
        if ($item->purchase_order_item_id) {
            $this->syncPurchaseOrderItem((int) $item->purchase_order_item_id);
        }
    }

    private function syncPurchaseOrderItem(int $orderItemId): void
    {
        $orderItem = PurchaseOrderItem::query()->find($orderItemId);

        if (! $orderItem) {
            return;
        }

        $invoicedQuantity = (int) $orderItem->purchaseItems()->sum('quantity');
        $orderItem->update(['received_quantity' => $invoicedQuantity]);

        $purchaseOrder = $orderItem->purchaseOrder()->first();

        if (! $purchaseOrder || in_array($purchaseOrder->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Approval, PurchaseOrderStatus::Cancelled], true)) {
            return;
        }

        $items = $purchaseOrder->items()->get(['quantity', 'received_quantity']);
        $allReceived = $items->every(fn ($orderItem) => $orderItem->received_quantity >= $orderItem->quantity);
        $anyReceived = $items->contains(fn ($orderItem) => $orderItem->received_quantity > 0);

        $newStatus = match (true) {
            $allReceived => PurchaseOrderStatus::Received,
            $anyReceived => PurchaseOrderStatus::Partial,
            default => PurchaseOrderStatus::Order,
        };

        if ($purchaseOrder->status !== $newStatus) {
            $purchaseOrder->update(['status' => $newStatus]);
        }
    }
}
