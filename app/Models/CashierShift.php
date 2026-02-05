<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierShift extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'user_id',
        'opening_time',
        'closing_time',
        'opening_cash',
        'expected_cash',
        'actual_cash',
        'difference',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_time' => 'datetime',
            'closing_time' => 'datetime',
            'opening_cash' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'actual_cash' => 'decimal:2',
            'difference' => 'decimal:2',
            'status' => ShiftStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'shift_id');
    }

    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class, 'shift_id');
    }

    public function isOpen(): bool
    {
        return $this->status === ShiftStatus::Open;
    }

    public function calculateExpectedCash(): float
    {
        // Hitung total penjualan tunai berdasarkan nilai transaksi (bukan amount yang dibayar)
        // Karena kembalian sudah dikembalikan ke pelanggan, yang masuk kas adalah nilai penjualan
        $cashSales = $this->sales()
            ->whereHas('payments', function ($q) {
                $q->whereHas('paymentMethod', fn ($q) => $q->where('is_cash', true));
            })
            ->sum('total');

        $cashIn = $this->cashMovements()->where('type', 'in')->sum('amount');
        $cashOut = $this->cashMovements()->where('type', 'out')->sum('amount');

        return $this->opening_cash + $cashSales + $cashIn - $cashOut;
    }

    /**
     * Hitung total penjualan tunai untuk shift ini
     */
    public function getCashSalesTotal(): float
    {
        return $this->sales()
            ->whereHas('payments', function ($q) {
                $q->whereHas('paymentMethod', fn ($q) => $q->where('is_cash', true));
            })
            ->sum('total');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', ShiftStatus::Open);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('opening_time', today());
    }
}
