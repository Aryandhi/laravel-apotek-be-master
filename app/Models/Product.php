<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'code',
        'barcode',
        'kfa_code',
        'name',
        'generic_name',
        'category_id',
        'base_unit_id',
        'purchase_price',
        'selling_price',
        'min_stock',
        'max_stock',
        'rack_location',
        'description',
        'requires_prescription',
        'is_active',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'purchase_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'min_stock' => 'integer',
            'max_stock' => 'integer',
            'requires_prescription' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function baseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(ProductBatch::class)
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->orderBy('expired_date', 'asc');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(UnitConversion::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function purchasePlanItem(): HasOne
    {
        return $this->hasOne(PurchasePlanItem::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->batches()->where('status', 'active')->sum('stock');
    }

    public function isLowStock(): bool
    {
        return $this->total_stock <= $this->min_stock;
    }

    /**
     * Supplier currently selected for this product in the Perencanaan (planning) tab.
     * Proxies to the related PurchasePlanItem so it can be used as an editable table column.
     */
    public function getPurchasePlanSupplierIdAttribute(): ?int
    {
        return $this->purchasePlanItem?->supplier_id;
    }

    public function setPurchasePlanSupplierIdAttribute(?int $value): void
    {
        if ($value === null) {
            $this->purchasePlanItem?->delete();

            return;
        }

        $this->setRelation(
            'purchasePlanItem',
            PurchasePlanItem::updateOrCreate(
                ['product_id' => $this->getKey()],
                ['supplier_id' => $value]
            )
        );
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('batches', function ($q) {
            $q->where('status', 'active');
        }, '<=', $this->min_stock);
    }

    public function scopeRequiresPrescription($query)
    {
        return $query->where('requires_prescription', true);
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return Storage::disk('public')->url($this->image);
    }
}
