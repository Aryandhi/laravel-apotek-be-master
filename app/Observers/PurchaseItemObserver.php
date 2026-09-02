<?php

namespace App\Observers;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseItem;

/**
 * Keeps Surat Pesanan (PurchaseOrder) item received quantities & status in sync
 * whenever an invoice item referencing it is created or removed.
 */
class PurchaseItemObserver
{
    public function created(PurchaseItem $item): void
    {
        if ($item->purchase_order_item_id) {
            $this->syncPurchaseOrder($item);
        }
    }

    public function deleted(PurchaseItem $item): void
    {
        if ($item->purchase_order_item_id) {
            $this->syncPurchaseOrder($item);
        }
    }

    private function syncPurchaseOrder(PurchaseItem $item): void
    {
        $orderItem = $item->purchaseOrderItem()->first();

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
