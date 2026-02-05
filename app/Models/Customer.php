<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'email',
        'points',
        'birth_date',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'points' => 'integer',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function saleReturns(): HasMany
    {
        return $this->hasMany(SaleReturn::class);
    }

    public function addPoints(int $points): void
    {
        $this->increment('points', $points);
    }

    public function deductPoints(int $points): void
    {
        $this->decrement('points', $points);
    }
}
