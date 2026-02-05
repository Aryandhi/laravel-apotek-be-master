<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SaleStatus;
use App\Enums\XenditPaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Models\XenditTransaction;
use App\Services\XenditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class XenditPaymentController extends Controller
{
    public function __construct(protected XenditService $xenditService) {}

    /**
     * Check if Xendit is enabled
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'enabled' => $this->xenditService->isEnabled(),
                'payment_methods' => $this->getXenditPaymentMethods(),
            ],
        ]);
    }

    /**
     * Create a new sale with Xendit payment
     * Returns invoice URL for payment
     */
    public function createSaleWithPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:product_batches,id',
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'payment_method_code' => 'required|string|in:QRIS,GOPAY,OVO,DANA,SHOPEEPAY,LINKAJA',
        ]);

        if (! $this->xenditService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Xendit tidak diaktifkan',
            ], 400);
        }

        $user = $request->user();

        if (! $user->activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan buka shift terlebih dahulu',
            ], 422);
        }

        try {
            $result = DB::transaction(function () use ($validated, $user) {
                // Calculate totals
                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $itemDiscount = $item['discount'] ?? 0;
                    $subtotal += ($item['price'] * $item['quantity']) - $itemDiscount;
                }

                $discount = $validated['discount'] ?? 0;
                $tax = $validated['tax'] ?? 0;
                $total = $subtotal - $discount + $tax;

                // Create sale with pending status
                $sale = Sale::create([
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'date' => now()->toDateString(),
                    'customer_id' => $validated['customer_id'] ?? null,
                    'user_id' => $user->id,
                    'shift_id' => $user->activeShift->id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'paid_amount' => 0,
                    'change_amount' => 0,
                    'status' => SaleStatus::Pending,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // Create sale items (don't reduce stock yet)
                foreach ($validated['items'] as $item) {
                    $itemDiscount = $item['discount'] ?? 0;
                    $itemSubtotal = ($item['price'] * $item['quantity']) - $itemDiscount;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_batch_id' => $item['batch_id'],
                        'unit_id' => $item['unit_id'] ?? null,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'discount' => $itemDiscount,
                        'subtotal' => $itemSubtotal,
                    ]);
                }

                // Create Xendit invoice
                $sale->load(['customer', 'items.product']);
                $transaction = $this->xenditService->createInvoice($sale, [
                    'payment_method' => $validated['payment_method_code'],
                ]);

                return [
                    'sale' => $sale,
                    'transaction' => $transaction,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice Xendit berhasil dibuat',
                'data' => [
                    'sale_id' => $result['sale']->id,
                    'invoice_number' => $result['sale']->invoice_number,
                    'total' => $result['sale']->total,
                    'xendit' => [
                        'transaction_id' => $result['transaction']->id,
                        'external_id' => $result['transaction']->external_id,
                        'invoice_url' => $result['transaction']->invoice_url,
                        'status' => $result['transaction']->status->value,
                        'expires_at' => $result['transaction']->expires_at->toIso8601String(),
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('[XENDIT API] Failed to create sale with payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat pembayaran: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Xendit invoice for existing sale
     */
    public function createInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'payment_method_code' => 'nullable|string|in:QRIS,GOPAY,OVO,DANA,SHOPEEPAY,LINKAJA',
        ]);

        if (! $this->xenditService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Xendit tidak diaktifkan',
            ], 400);
        }

        $sale = Sale::with(['customer', 'items.product'])->findOrFail($validated['sale_id']);

        // Check for existing pending transaction
        $existingTransaction = XenditTransaction::where('sale_id', $sale->id)
            ->where('status', XenditPaymentStatus::Pending)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingTransaction) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice sudah ada',
                'data' => [
                    'transaction_id' => $existingTransaction->id,
                    'external_id' => $existingTransaction->external_id,
                    'invoice_url' => $existingTransaction->invoice_url,
                    'amount' => (float) $existingTransaction->amount,
                    'status' => $existingTransaction->status->value,
                    'expires_at' => $existingTransaction->expires_at->toIso8601String(),
                ],
            ]);
        }

        try {
            $transaction = $this->xenditService->createInvoice($sale, [
                'payment_method' => $validated['payment_method_code'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'external_id' => $transaction->external_id,
                    'invoice_url' => $transaction->invoice_url,
                    'amount' => (float) $transaction->amount,
                    'status' => $transaction->status->value,
                    'expires_at' => $transaction->expires_at->toIso8601String(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('[XENDIT API] Failed to create invoice', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat invoice: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkStatus(Request $request, XenditTransaction $transaction): JsonResponse
    {
        if (! $this->xenditService->isEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Xendit tidak diaktifkan',
            ], 400);
        }

        // If already paid, return immediately
        if ($transaction->isPaid()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $transaction->status->value,
                    'is_paid' => true,
                    'is_expired' => false,
                    'paid_at' => $transaction->paid_at?->toIso8601String(),
                    'payment_method' => $transaction->payment_method,
                    'payment_channel' => $transaction->payment_channel,
                    'sale_id' => $transaction->sale_id,
                ],
            ]);
        }

        // Sync with Xendit if we have xendit_id
        if ($transaction->xendit_id) {
            $result = $this->xenditService->getInvoiceStatus($transaction->xendit_id);

            if ($result['success']) {
                $xenditStatus = $result['status'];

                if (in_array($xenditStatus, ['PAID', 'SETTLED'])) {
                    $transaction->markAsPaid(['sync' => $result['data']]);

                    // Complete the sale
                    $this->completeSale($transaction);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'status' => 'PAID',
                            'is_paid' => true,
                            'is_expired' => false,
                            'paid_at' => $transaction->fresh()->paid_at?->toIso8601String(),
                            'sale_id' => $transaction->sale_id,
                        ],
                    ]);
                }

                if ($xenditStatus === 'EXPIRED') {
                    $transaction->markAsExpired();
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $transaction->status->value,
                'is_paid' => false,
                'is_expired' => $transaction->isExpired(),
                'expires_at' => $transaction->expires_at?->toIso8601String(),
                'sale_id' => $transaction->sale_id,
            ],
        ]);
    }

    /**
     * Cancel/expire a pending payment
     */
    public function cancel(Request $request, XenditTransaction $transaction): JsonResponse
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

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
        }

        $transaction->markAsExpired();

        // Cancel the sale if it's still pending
        if ($transaction->sale && $transaction->sale->status === SaleStatus::Pending) {
            $transaction->sale->update(['status' => SaleStatus::Cancelled]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembayaran berhasil dibatalkan',
        ]);
    }

    /**
     * Get user's Xendit transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = XenditTransaction::query()
            ->whereHas('sale', fn ($q) => $q->where('user_id', $user->id))
            ->with('sale:id,invoice_number,total')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $transactions = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions->through(fn ($tx) => [
                'id' => $tx->id,
                'external_id' => $tx->external_id,
                'sale' => $tx->sale ? [
                    'id' => $tx->sale->id,
                    'invoice_number' => $tx->sale->invoice_number,
                    'total' => $tx->sale->total,
                ] : null,
                'amount' => (float) $tx->amount,
                'payment_method' => $tx->payment_method,
                'payment_channel' => $tx->payment_channel,
                'status' => $tx->status->value,
                'paid_at' => $tx->paid_at?->toIso8601String(),
                'created_at' => $tx->created_at->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Get Xendit payment methods available
     */
    private function getXenditPaymentMethods(): array
    {
        return [
            ['code' => 'QRIS', 'name' => 'QRIS', 'icon' => 'qris'],
            ['code' => 'GOPAY', 'name' => 'GoPay', 'icon' => 'gopay'],
            ['code' => 'OVO', 'name' => 'OVO', 'icon' => 'ovo'],
            ['code' => 'DANA', 'name' => 'DANA', 'icon' => 'dana'],
            ['code' => 'SHOPEEPAY', 'name' => 'ShopeePay', 'icon' => 'shopeepay'],
            ['code' => 'LINKAJA', 'name' => 'LinkAja', 'icon' => 'linkaja'],
        ];
    }

    /**
     * Complete sale after payment success
     */
    private function completeSale(XenditTransaction $transaction): void
    {
        $sale = $transaction->sale;

        if (! $sale || $sale->status === SaleStatus::Completed) {
            return;
        }

        DB::transaction(function () use ($sale, $transaction) {
            // Update sale status
            $sale->update([
                'status' => SaleStatus::Completed,
                'paid_amount' => $sale->total,
                'change_amount' => 0,
            ]);

            // Find or create payment method for Xendit
            $paymentMethod = PaymentMethod::firstOrCreate(
                ['code' => $transaction->payment_method ?? 'XENDIT'],
                [
                    'name' => $transaction->payment_channel ?? 'Xendit',
                    'is_active' => true,
                    'is_cash' => false,
                ]
            );

            // Create payment record
            SalePayment::create([
                'sale_id' => $sale->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $sale->total,
                'reference_number' => $transaction->external_id,
            ]);

            // Reduce stock
            foreach ($sale->items as $item) {
                $batch = ProductBatch::find($item->product_batch_id);
                if ($batch) {
                    $batch->decrement('stock', $item->quantity);

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'product_batch_id' => $item->product_batch_id,
                        'type' => 'sale',
                        'quantity' => -$item->quantity,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => "Sale #{$sale->invoice_number}",
                        'user_id' => $sale->user_id,
                    ]);
                }
            }
        });
    }

    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastSale = Sale::whereDate('date', today())->orderByDesc('id')->first();
        $sequence = $lastSale ? ((int) substr($lastSale->invoice_number, -4)) + 1 : 1;

        return "INV-{$date}-".str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
