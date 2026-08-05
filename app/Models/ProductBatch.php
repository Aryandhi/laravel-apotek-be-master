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

    protected static function booted(): void
    {
        static::saving(function (ProductBatch $productBatch): void {
            $productBatch->syncStatusFromExpiryDate();
        });
    }

    protected $fillable = [
        'product_id',
        'batch_number',
        'expired_date',
        'purchase_price',
        'margin_percentage',
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
            'margin_percentage' => 'decimal:2',
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

    public function syncStatusFromExpiryDate(): void
    {
        $status = $this->status instanceof BatchStatus ? $this->status : ($this->status ? BatchStatus::from($this->status) : null);

        if (in_array($status, [BatchStatus::Returned, BatchStatus::Damaged], true)) {
            return;
        }

        $today = now()->startOfDay();
        $nearExpiredDays = (int) Setting::get('near_expired_days', 90);

        if ($this->expired_date->lt($today)) {
            $this->status = BatchStatus::Expired;

            return;
        }

        if ($this->expired_date->lte($today->copy()->addDays($nearExpiredDays))) {
            $this->status = BatchStatus::NearExpired;

            return;
        }

        $this->status = BatchStatus::Active;
    }

    public static function syncExpiryStatuses(?int $nearExpiredDays = null): array
    {
        $nearExpiredDays ??= (int) Setting::get('near_expired_days', 90);

        $today = now()->startOfDay();
        $nearExpiredLimit = $today->copy()->addDays($nearExpiredDays);

        $expiredCount = static::query()
            ->whereNotIn('status', [BatchStatus::Returned, BatchStatus::Damaged])
            ->where('expired_date', '<', $today)
            ->update(['status' => BatchStatus::Expired]);

        $nearExpiredCount = static::query()
            ->whereNotIn('status', [BatchStatus::Returned, BatchStatus::Damaged])
            ->where('expired_date', '>=', $today)
            ->where('expired_date', '<=', $nearExpiredLimit)
            ->update(['status' => BatchStatus::NearExpired]);

        $activeCount = static::query()
            ->whereNotIn('status', [BatchStatus::Returned, BatchStatus::Damaged])
            ->where('expired_date', '>', $nearExpiredLimit)
            ->update(['status' => BatchStatus::Active]);

        return [
            'expired' => $expiredCount,
            'near_expired' => $nearExpiredCount,
            'active' => $activeCount,
            'total_updated' => $expiredCount + $nearExpiredCount + $activeCount,
        ];
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
