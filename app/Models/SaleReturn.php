<?php

namespace App\Models;

use App\Enums\RefundMethod;
use App\Enums\SaleReturnStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'code',
        'sale_id',
        'customer_id',
        'date',
        'reason',
        'total',
        'refund_method',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total' => 'decimal:2',
            'status' => SaleReturnStatus::class,
            'refund_method' => RefundMethod::class,
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleReturnItem::class);
    }

    public function calculateTotal(): float
    {
        return $this->items->sum('subtotal');
    }
}
