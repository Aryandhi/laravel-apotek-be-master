<?php

namespace App\Models;

use App\Enums\CategoryType as CategoryTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'category_type_id',
        'requires_prescription',
        'is_narcotic',
    ];

    protected function casts(): array
    {
        return [
            'type' => CategoryTypeEnum::class,
            'requires_prescription' => 'boolean',
            'is_narcotic' => 'boolean',
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categoryType(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class);
    }
}
