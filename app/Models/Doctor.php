<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sip_number',
        'specialization',
        'phone',
        'hospital_clinic',
        'address',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function salePrescriptions(): HasMany
    {
        return $this->hasMany(SalePrescription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
