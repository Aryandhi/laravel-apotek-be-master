<?php

namespace App\Services;

use App\Enums\XenditPaymentStatus;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\XenditTransaction;
use Illuminate\Support\Facades\Log;
use Xendit\BalanceAndTransaction\BalanceApi;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;

class XenditService
{
    protected InvoiceApi $invoiceApi;

    protected BalanceApi $balanceApi;

    public function __construct()
    {
        if ($this->isEnabled()) {
            Configuration::setXenditKey($this->getSecretKey());
            $this->invoiceApi = new InvoiceApi;
            $this->balanceApi = new BalanceApi;
            Log::debug('[XENDIT SERVICE] Initialized with secret key');
        }
    }

    /**
     * Get Xendit secret key from database or config
     */
    public function getSecretKey(): ?string
    {
        return Setting::get('xendit_secret_key', config('xendit.secret_key'));
    }

    /**
     * Get invoice duration from database or config
     */
    public function getInvoiceDuration(): int
    {
        return (int) Setting::get('xendit_invoice_duration', config('xendit.invoice.duration', 3600));
    }

    public function isEnabled(): bool
    {
        // Check database first, fallback to config
        $enabledFromDb = Setting::get('xendit_enabled');

        if ($enabledFromDb !== null) {
            $enabled = $enabledFromDb === 'true' || $enabledFromDb === '1' || $enabledFromDb === true;
        } else {
            $enabled = config('xendit.enabled', false);
        }

        return $enabled && ! empty($this->getSecretKey());
    }

    public function testConnection(?string $secretKey = null): array
    {
        try {
            $key = $secretKey ?? $this->getSecretKey();

            if (empty($key)) {
                return [
                    'success' => false,
                    'message' => 'Secret key tidak ditemukan',
                ];
            }

            Configuration::setXenditKey($key);

            // Try Balance API first
            try {
                $balanceApi = new BalanceApi;
                $balance = $balanceApi->getBalance('CASH');

                return [
                    'success' => true,
                    'message' => 'Koneksi ke Xendit berhasil',
                    'data' => [
                        'balance' => $balance->getBalance(),
                        'environment' => str_contains($key, 'development') ? 'development' : 'production',
                    ],
                ];
            } catch (XenditSdkException $e) {
                // Balance API might not have permission, try Invoice API
                $invoiceApi = new InvoiceApi;
                $invoiceApi->getInvoices(null, null, null, 1);

                return [
                    'success' => true,
                    'message' => 'Koneksi ke Xendit berhasil',
                    'data' => [
                        'environment' => str_contains($key, 'development') ? 'development' : 'production',
                    ],
                ];
            }
        } catch (XenditSdkException $e) {
            return [
                'success' => false,
                'message' => 'API Key tidak valid: '.$e->getMessage(),
                'error' => $e->getFullError(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ];
        }
    }

    public function createInvoice(Sale $sale, array $options = []): XenditTransaction
    {
        $externalId = XenditTransaction::generateExternalId();
        $duration = $this->getInvoiceDuration();

        Log::info('[XENDIT SERVICE] createInvoice called', [
            'sale_id' => $sale->id,
            'external_id' => $externalId,
            'duration' => $duration,
            'amount' => $sale->total,
        ]);

        $invoiceData = [
            'external_id' => $externalId,
            'amount' => (int) $sale->total,
            'description' => 'Pembayaran '.$sale->invoice_number,
            'invoice_duration' => $duration,
            'currency' => config('xendit.invoice.currency', 'IDR'),
            'customer' => $this->buildCustomerData($sale),
            'items' => $this->buildItemsData($sale),
        ];

        if (! empty($options['success_redirect_url'])) {
            $invoiceData['success_redirect_url'] = $options['success_redirect_url'];
        }

        if (! empty($options['failure_redirect_url'])) {
            $invoiceData['failure_redirect_url'] = $options['failure_redirect_url'];
        }

        Log::info('[XENDIT SERVICE] Invoice data prepared', [
            'invoice_data' => $invoiceData,
        ]);

        $createInvoiceRequest = new CreateInvoiceRequest($invoiceData);

        try {
            Log::info('[XENDIT SERVICE] Calling Xendit API...');
            $invoice = $this->invoiceApi->createInvoice($createInvoiceRequest);

            Log::info('[XENDIT SERVICE] Xendit API response', [
                'invoice_id' => $invoice->getId(),
                'invoice_url' => $invoice->getInvoiceUrl(),
            ]);

            $transaction = XenditTransaction::create([
                'sale_id' => $sale->id,
                'external_id' => $externalId,
                'xendit_id' => $invoice->getId(),
                'invoice_url' => $invoice->getInvoiceUrl(),
                'payment_method' => 'INVOICE',
                'amount' => $sale->total,
                'status' => XenditPaymentStatus::Pending,
                'expires_at' => now()->addSeconds($duration),
                'xendit_response' => [
                    'created' => $invoice->jsonSerialize(),
                ],
            ]);

            Log::info('[XENDIT SERVICE] Transaction created', [
                'transaction_id' => $transaction->id,
            ]);

            return $transaction;
        } catch (XenditSdkException $e) {
            Log::error('[XENDIT SERVICE] Xendit SDK Exception', [
                'message' => $e->getMessage(),
                'full_error' => $e->getFullError(),
            ]);
            throw new \Exception('Gagal membuat invoice Xendit: '.$e->getMessage());
        }
    }

    public function getInvoiceStatus(string $invoiceId): array
    {
        try {
            $invoice = $this->invoiceApi->getInvoiceById($invoiceId);

            return [
                'success' => true,
                'status' => $invoice->getStatus(),
                'data' => $invoice->jsonSerialize(),
            ];
        } catch (XenditSdkException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getFullError(),
            ];
        }
    }

    public function expireInvoice(string $invoiceId): array
    {
        try {
            $invoice = $this->invoiceApi->expireInvoice($invoiceId);

            return [
                'success' => true,
                'message' => 'Invoice berhasil dibatalkan',
                'data' => $invoice->jsonSerialize(),
            ];
        } catch (XenditSdkException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getFullError(),
            ];
        }
    }

    public function handleWebhook(array $payload): ?XenditTransaction
    {
        $externalId = $payload['external_id'] ?? null;

        if (! $externalId) {
            return null;
        }

        $transaction = XenditTransaction::where('external_id', $externalId)->first();

        if (! $transaction) {
            return null;
        }

        $status = $payload['status'] ?? null;

        switch ($status) {
            case 'PAID':
            case 'SETTLED':
                $transaction->markAsPaid([
                    'webhook' => $payload,
                    'payment_method' => $payload['payment_method'] ?? null,
                    'payment_channel' => $payload['payment_channel'] ?? null,
                ]);

                $transaction->update([
                    'payment_method' => $payload['payment_method'] ?? $transaction->payment_method,
                    'payment_channel' => $payload['payment_channel'] ?? $transaction->payment_channel,
                ]);

                if ($transaction->sale) {
                    $transaction->sale->update([
                        'paid_amount' => $transaction->sale->total,
                        'change_amount' => 0,
                    ]);
                }
                break;

            case 'EXPIRED':
                $transaction->markAsExpired();
                break;

            case 'FAILED':
                $transaction->markAsFailed(['webhook' => $payload]);
                break;
        }

        return $transaction->fresh();
    }

    protected function buildCustomerData(Sale $sale): array
    {
        $customer = $sale->customer;

        if (! $customer) {
            return [
                'given_names' => 'Customer',
            ];
        }

        return array_filter([
            'given_names' => $customer->name,
            'email' => $customer->email,
            'mobile_number' => $customer->phone,
        ]);
    }

    protected function buildItemsData(Sale $sale): array
    {
        return $sale->items->map(function ($item) {
            return [
                'name' => $item->product?->name ?? 'Product',
                'quantity' => (int) $item->quantity,
                'price' => (int) $item->price,
            ];
        })->toArray();
    }
}
