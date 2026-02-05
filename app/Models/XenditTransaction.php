<?php

namespace App\Models;

use App\Enums\XenditPaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class XenditTransaction extends Model
{
    protected $fillable = [
        'sale_id',
        'external_id',
        'xendit_id',
        'invoice_url',
        'payment_method',
        'payment_channel',
        'amount',
        'status',
        'xendit_response',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => XenditPaymentStatus::class,
            'xendit_response' => 'array',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function isPaid(): bool
    {
        return $this->status?->isPaid() ?? false;
    }

    public function isPending(): bool
    {
        return $this->status === XenditPaymentStatus::Pending;
    }

    public function isExpired(): bool
    {
        return $this->status === XenditPaymentStatus::Expired;
    }

    public function markAsPaid(array $response = []): void
    {
        $this->update([
            'status' => XenditPaymentStatus::Paid,
            'paid_at' => now(),
            'xendit_response' => array_merge($this->xendit_response ?? [], $response),
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'status' => XenditPaymentStatus::Expired,
        ]);
    }

    public function markAsFailed(array $response = []): void
    {
        $this->update([
            'status' => XenditPaymentStatus::Failed,
            'xendit_response' => array_merge($this->xendit_response ?? [], $response),
        ]);
    }

    public static function generateExternalId(): string
    {
        return 'INV-'.now()->format('YmdHis').'-'.strtoupper(substr(uniqid(), -6));
    }

    public function scopePending($query)
    {
        return $query->where('status', XenditPaymentStatus::Pending);
    }

    public function scopePaid($query)
    {
        return $query->whereIn('status', [XenditPaymentStatus::Paid, XenditPaymentStatus::Settled]);
    }
}
