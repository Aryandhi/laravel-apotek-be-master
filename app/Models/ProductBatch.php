<?php

namespace App\Models;

use App\Enums\BatchStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'batch_number',
        'expired_date',
        'purchase_price',
        'selling_price',
        'stock',
        'initial_stock',
        'supplier_id',
        'purchase_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expired_date' => 'date',
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'stock' => 'integer',
            'initial_stock' => 'integer',
            'status' => BatchStatus::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isExpired(): bool
    {
        return $this->expired_date->isPast();
    }

    public function isNearExpired(?int $days = null): bool
    {
        $days = $days ?? (int) Setting::get('near_expired_days', 90);

        return $this->expired_date->diffInDays(now()) <= $days && ! $this->isExpired();
    }

    public function daysUntilExpired(): int
    {
        return max(0, $this->expired_date->diffInDays(now(), false) * -1);
    }

    public function scopeActive($query)
    {
        return $query->where('status', BatchStatus::Active);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', BatchStatus::Active)->where('stock', '>', 0);
    }

    public function scopeExpired($query)
    {
        return $query->where('expired_date', '<', now());
    }

    public function scopeNearExpired($query, ?int $days = null)
    {
        $days = $days ?? (int) Setting::get('near_expired_days', 90);

        return $query->whereBetween('expired_date', [now(), now()->addDays($days)]);
    }

    public function scopeFefo($query)
    {
        return $query->orderBy('expired_date', 'asc');
    }
}
