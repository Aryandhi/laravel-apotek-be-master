<?php

namespace App\Models;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'po_number',
        'title',
        'group',
        'supplier_id',
        'status',
        'order_date',
        'notes',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'group' => PurchaseOrderGroup::class,
            'status' => PurchaseOrderStatus::class,
            'order_date' => 'date',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopeStatus($query, PurchaseOrderStatus $status)
    {
        return $query->where('status', $status);
    }
}
