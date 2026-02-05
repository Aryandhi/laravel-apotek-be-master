<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'store_id',
        'key',
        'value',
        'group',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public static function get(string $key, mixed $default = null, ?int $storeId = null): mixed
    {
        $query = static::where('key', $key);

        if ($storeId) {
            $query->where('store_id', $storeId);
        } else {
            $query->whereNull('store_id');
        }

        return $query->value('value') ?? $default;
    }

    public static function set(string $key, mixed $value, ?int $storeId = null, string $group = 'general'): static
    {
        return static::updateOrCreate(
            ['key' => $key, 'store_id' => $storeId],
            ['value' => $value, 'group' => $group]
        );
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopeGlobal($query)
    {
        return $query->whereNull('store_id');
    }
}
