<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\XenditTransaction;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditPaymentController extends Controller
{
    public function __construct(protected XenditService $xenditService) {}

    public function createInvoice(Request $request): JsonResponse
    {
        Log::info('[XENDIT] createInvoice called', [
            'sale_id' => $request->sale_id,
            'all_input' => $request->all(),
        ]);

        $request->validate([
            'sale_id' => 'required|exists:sales,id',
        ]);

        if (! $this->xenditService->isEnabled()) {
            Log::warning('[XENDIT] Xendit is not enabled');

            return response()->json([
                'success' => false,
                'message' => 'Xendit tidak diaktifkan',
            ], 400);
        }

        $sale = Sale::with(['customer', 'items.product'])->findOrFail($request->sale_id);
        Log::info('[XENDIT] Sale loaded', [
            'sale_id' => $sale->id,
            'invoice_number' => $sale->invoice_number,
            'total' => $sale->total,
            'customer' => $sale->customer?->name,
            'items_count' => $sale->items->count(),
        ]);

        $existingTransaction = XenditTransaction::where('sale_id', $sale->id)
            ->pending()
            ->first();

        if ($existingTransaction && $existingTransaction->expires_at > now()) {
            Log::info('[XENDIT] Returning existing transaction', [
                'transaction_id' => $existingTransaction->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice sudah ada',
                'data' => [
                    'transaction_id' => $existingTransaction->id,
                    'external_id' => $existingTransaction->external_id,
                    'invoice_url' => $existingTransaction->invoice_url,
                    'amount' => $existingTransaction->amount,
                    'status' => $existingTransaction->status->value,
                    'expires_at' => $existingTransaction->expires_at->toIso8601String(),
                ],
            ]);
        }

        try {
            Log::info('[XENDIT] Creating new invoice...');
            $transaction = $this->xenditService->createInvoice($sale, [
                'success_redirect_url' => config('xendit.invoice.success_redirect_url'),
                'failure_redirect_url' => config('xendit.invoice.failure_redirect_url'),
            ]);

            Log::info('[XENDIT] Invoice created successfully', [
                'transaction_id' => $transaction->id,
                'external_id' => $transaction->external_id,
                'invoice_url' => $transaction->invoice_url,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'external_id' => $transaction->external_id,
                    'invoice_url' => $transaction->invoice_url,
                    'amount' => $transaction->amount,
                    'status' => $transaction->status->value,
                    'expires_at' => $transaction->expires_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[XENDIT] Failed to create invoice', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkStatus(XenditTransaction $transaction): JsonResponse
    {
        if (! $this->xenditService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Xendit tidak diaktifkan',
            ], 400);
        }

        if ($transaction->isPaid()) {
            return response()->json([
                'success' => true,
                'status' => $transaction->status->value,
                'is_paid' => true,
                'paid_at' => $transaction->paid_at?->toIso8601String(),
                'payment_method' => $transaction->payment_method,
                'payment_channel' => $transaction->payment_channel,
            ]);
        }

        if ($transaction->xendit_id) {
            $result = $this->xenditService->getInvoiceStatus($transaction->xendit_id);

            if ($result['success']) {
                $xenditStatus = $result['status'];

                if (in_array($xenditStatus, ['PAID', 'SETTLED'])) {
                    $transaction->markAsPaid(['sync' => $result['data']]);

                    return response()->json([
                        'success' => true,
                        'status' => 'PAID',
                        'is_paid' => true,
                        'paid_at' => $transaction->fresh()->paid_at?->toIso8601String(),
                    ]);
                }

                if ($xenditStatus === 'EXPIRED') {
                    $transaction->markAsExpired();
                }
            }
        }

        return response()->json([
            'success' => true,
            'status' => $transaction->status->value,
            'is_paid' => $transaction->isPaid(),
            'is_expired' => $transaction->isExpired(),
            'expires_at' => $transaction->expires_at?->toIso8601String(),
        ]);
    }

    public function cancel(XenditTransaction $transaction): JsonResponse
    {
        if (! $this->xenditService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Xendit tidak diaktifkan',
            ], 400);
        }

        if ($transaction->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan invoice yang sudah dibayar',
            ], 400);
        }

        if ($transaction->xendit_id) {
            $result = $this->xenditService->expireInvoice($transaction->xendit_id);

            if ($result['success']) {
                $transaction->markAsExpired();

                return response()->json([
                    'success' => true,
                    'message' => 'Invoice berhasil dibatalkan',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 400);
        }

        $transaction->markAsExpired();

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dibatalkan',
        ]);
    }

    public function isEnabled(): JsonResponse
    {
        return response()->json([
            'enabled' => $this->xenditService->isEnabled(),
        ]);
    }
}
