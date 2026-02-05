<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalePrescription extends Model
{
    protected $fillable = [
        'sale_id',
        'prescription_number',
        'doctor_id',
        'patient_name',
        'patient_age',
        'patient_address',
        'diagnosis',
        'date',
        'is_copy',
        'copy_number',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'patient_age' => 'integer',
            'is_copy' => 'boolean',
            'copy_number' => 'integer',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}
