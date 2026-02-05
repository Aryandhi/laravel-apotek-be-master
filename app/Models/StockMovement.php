<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    protected $fillable = [
        'product_batch_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
        ];
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function isIncoming(): bool
    {
        return $this->type->isIncoming();
    }

    public function scopeIncoming($query)
    {
        return $query->whereIn('type', [
            StockMovementType::Purchase,
            StockMovementType::ReturnCustomer,
            StockMovementType::AdjustmentIn,
            StockMovementType::TransferIn,
        ]);
    }

    public function scopeOutgoing($query)
    {
        return $query->whereIn('type', [
            StockMovementType::Sale,
            StockMovementType::ReturnSupplier,
            StockMovementType::AdjustmentOut,
            StockMovementType::TransferOut,
            StockMovementType::Damaged,
            StockMovementType::Expired,
        ]);
    }
}
