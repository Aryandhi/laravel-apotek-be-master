<?php

namespace App\Models;

use App\Enums\CompoundType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompoundedItem extends Model
{
    protected $fillable = [
        'sale_item_id',
        'name',
        'type',
        'quantity',
        'price',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'type' => CompoundType::class,
            'quantity' => 'integer',
            'price' => 'decimal:2',
        ];
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(CompoundedItemDetail::class);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->details->sum(fn($d) => $d->quantity * $d->price);
    }
}
