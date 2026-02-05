<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'sale_prescription_id',
        'product_id',
        'quantity',
        'dosage',
        'frequency',
        'duration',
        'instructions',
        'is_compounded',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'is_compounded' => 'boolean',
        ];
    }

    public function salePrescription(): BelongsTo
    {
        return $this->belongsTo(SalePrescription::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getFullInstructionsAttribute(): string
    {
        $parts = [];

        if ($this->dosage) {
            $parts[] = $this->dosage;
        }

        if ($this->frequency) {
            $parts[] = $this->frequency;
        }

        if ($this->duration) {
            $parts[] = "selama {$this->duration}";
        }

        if ($this->instructions) {
            $parts[] = $this->instructions;
        }

        return implode(' - ', $parts);
    }
}
