<?php

namespace App\Models;

use App\Observers\PurchaseItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(PurchaseItemObserver::class)]
class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'purchase_order_item_id',
        'batch_number',
        'expired_date',
        'quantity',
        'unit_id',
        'purchase_price',
        'margin_percentage',
        'selling_price',
        'subtotal',
        'discount',
        'total',
        'received_quantity',
    ];

    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
            'quantity' => 'integer',
            'purchase_price' => 'decimal:2',
            'margin_percentage' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'received_quantity' => 'integer',
        ];
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->quantity;
    }

    public function getRemainingQuantityAttribute(): int
    {
        return $this->quantity - $this->received_quantity;
    }
}
