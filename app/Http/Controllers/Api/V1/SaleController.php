<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SaleStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Services\StockAllocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Sale::query()
            ->with(['customer', 'user'])
            ->where('user_id', $request->user()->id);

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->orderByDesc('date')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sales->through(fn ($sale) => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'date' => $sale->date->format('Y-m-d'),
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                ] : null,
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'status' => $sale->status->value,
            ]),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);
    }

    public function show(Sale $sale): JsonResponse
    {
        $sale->load(['customer', 'user', 'items.product', 'items.productBatch', 'items.unit', 'payments.paymentMethod']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'date' => $sale->date->format('Y-m-d'),
                'customer' => $sale->customer ? [
                    'id' => $sale->customer->id,
                    'name' => $sale->customer->name,
                    'phone' => $sale->customer->phone,
                ] : null,
                'cashier' => [
                    'id' => $sale->user->id,
                    'name' => $sale->user->name,
                ],
                'items' => $sale->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'code' => $item->product->code,
                    ],
                    'batch_number' => $item->productBatch?->batch_number,
                    'unit' => $item->unit?->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'subtotal' => $item->subtotal,
                ]),
                'payments' => $sale->payments->map(fn ($payment) => [
                    'id' => $payment->id,
                    'payment_method' => $payment->paymentMethod ? [
                        'id' => $payment->paymentMethod->id,
                        'name' => $payment->paymentMethod->name,
                    ] : null,
                    'amount' => $payment->amount,
                    'reference' => $payment->reference_number,
                ]),
                'subtotal' => $sale->subtotal,
                'discount' => $sale->discount,
                'tax' => $sale->tax,
                'total' => $sale->total,
                'status' => $sale->status->value,
                'paid_amount' => $sale->paid_amount,
                'change_amount' => $sale->change_amount,
                'notes' => $sale->notes,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'nullable|exists:product_batches,id', // Now optional - will auto-allocate
            'items.*.unit_id' => 'nullable|exists:units,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'payments' => 'required|array|min:1',
            'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
            'payments.*.amount' => 'required|numeric|min:0',
            'payments.*.reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        if (! $user->activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Please open a shift before creating sales',
            ], 422);
        }

        // Validate stock availability using StockAllocationService
        $stockService = app(StockAllocationService::class);
        $stockErrors = [];

        foreach ($validated['items'] as $index => $item) {
            $validation = $stockService->validateStock($item['product_id'], $item['quantity']);
            if (! $validation['available']) {
                $stockErrors[] = 'Item #'.($index + 1).': '.$validation['message'];
            }
        }

        if (! empty($stockErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak mencukupi',
                'errors' => $stockErrors,
            ], 422);
        }

        try {
            $sale = DB::transaction(function () use ($validated, $user, $stockService) {
                $subtotal = 0;
                foreach ($validated['items'] as $item) {
                    $itemDiscount = $item['discount'] ?? 0;
                    $subtotal += ($item['price'] * $item['quantity']) - $itemDiscount;
                }

                $discount = $validated['discount'] ?? 0;
                $tax = $validated['tax'] ?? 0;
                $total = $subtotal - $discount + $tax;

                $totalPayment = collect($validated['payments'])->sum('amount');
                $changeAmount = max(0, $totalPayment - $total);

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
                    'paid_amount' => $totalPayment,
                    'change_amount' => $changeAmount,
                    'status' => SaleStatus::Completed,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($validated['items'] as $item) {
                    $itemDiscount = $item['discount'] ?? 0;
                    $preferredBatchId = $item['batch_id'] ?? null;

                    // Allocate stock from multiple batches if needed
                    $allocation = $stockService->allocateStock(
                        $item['product_id'],
                        $item['quantity'],
                        $preferredBatchId
                    );

                    if (! $allocation['success']) {
                        throw new \Exception($allocation['message']);
                    }

                    // Create sale items and deduct stock for each batch allocation
                    foreach ($allocation['allocations'] as $alloc) {
                        $allocSubtotal = ($item['price'] * $alloc['quantity']) - ($itemDiscount * $alloc['quantity'] / $item['quantity']);

                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'product_id' => $item['product_id'],
                            'product_batch_id' => $alloc['batch_id'],
                            'unit_id' => $item['unit_id'] ?? null,
                            'quantity' => $alloc['quantity'],
                            'price' => $item['price'],
                            'discount' => $itemDiscount * $alloc['quantity'] / $item['quantity'],
                            'subtotal' => $allocSubtotal,
                        ]);

                        // Deduct stock from batch
                        $batch = ProductBatch::find($alloc['batch_id']);
                        $batch->decrement('stock', $alloc['quantity']);

                        // Record stock movement
                        StockMovement::create([
                            'product_id' => $item['product_id'],
                            'product_batch_id' => $alloc['batch_id'],
                            'type' => 'sale',
                            'quantity' => -$alloc['quantity'],
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'notes' => "Sale #{$sale->invoice_number}",
                            'user_id' => $user->id,
                        ]);
                    }
                }

                $cashPaymentMethodIds = PaymentMethod::where('is_cash', true)->pluck('id')->toArray();
                $cashPayment = 0;

                foreach ($validated['payments'] as $payment) {
                    SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_method_id' => $payment['payment_method_id'],
                        'amount' => $payment['amount'],
                        'reference_number' => $payment['reference_number'] ?? null,
                    ]);

                    if (in_array($payment['payment_method_id'], $cashPaymentMethodIds)) {
                        $cashPayment += $payment['amount'];
                    }
                }

                if ($cashPayment > 0) {
                    $user->activeShift->increment('expected_cash', $cashPayment);
                }

                return $sale;
            });

            return response()->json([
                'success' => true,
                'message' => 'Sale created successfully',
                'data' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'total' => $sale->total,
                    'change' => max(0, collect($validated['payments'])->sum('amount') - $sale->total),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sale: '.$e->getMessage(),
            ], 500);
        }
    }

    public function void(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();

        // Check if user owns this sale or is admin
        if ($sale->user_id !== $user->id && $user->role->value !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk membatalkan transaksi ini',
            ], 403);
        }

        // Check if sale can be voided
        if ($sale->status !== SaleStatus::Completed) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi selesai yang dapat dibatalkan',
            ], 422);
        }

        // Check if sale is from today (only allow void same day)
        if (! $sale->date->isToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya transaksi hari ini yang dapat dibatalkan',
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($sale, $validated, $user) {
                // Restore stock for each item
                foreach ($sale->items as $item) {
                    $batch = ProductBatch::find($item->product_batch_id);
                    if ($batch) {
                        $batch->increment('stock', $item->quantity);

                        StockMovement::create([
                            'product_id' => $item->product_id,
                            'product_batch_id' => $item->product_batch_id,
                            'type' => 'void',
                            'quantity' => $item->quantity,
                            'reference_type' => Sale::class,
                            'reference_id' => $sale->id,
                            'notes' => "Void #{$sale->invoice_number}: {$validated['reason']}",
                            'user_id' => $user->id,
                        ]);
                    }
                }

                // Update sale status
                $sale->update([
                    'status' => SaleStatus::Cancelled,
                    'notes' => ($sale->notes ? $sale->notes."\n" : '')."[VOID] {$validated['reason']}",
                ]);

                // Update shift expected cash if cash payment
                if ($sale->shift) {
                    $cashPayment = $sale->payments()
                        ->whereHas('paymentMethod', fn ($q) => $q->where('is_cash', true))
                        ->sum('amount');

                    if ($cashPayment > 0) {
                        $sale->shift->decrement('expected_cash', $cashPayment);
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibatalkan',
                'data' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'status' => SaleStatus::Cancelled->value,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membatalkan transaksi: '.$e->getMessage(),
            ], 500);
        }
    }

    public function receipt(Sale $sale): JsonResponse
    {
        $sale->load([
            'customer',
            'user',
            'items.product',
            'items.productBatch',
            'items.unit',
            'payments.paymentMethod',
            'shift',
        ]);

        // Get store info
        $store = $sale->user->store ?? \App\Models\Store::first();

        return response()->json([
            'success' => true,
            'data' => [
                'store' => $store ? [
                    'name' => $store->name,
                    'address' => $store->address,
                    'phone' => $store->phone,
                    'sia_number' => $store->sia_number,
                    'pharmacist_name' => $store->pharmacist_name,
                    'pharmacist_sipa' => $store->pharmacist_sipa,
                    'receipt_footer' => $store->receipt_footer,
                ] : null,
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'date' => $sale->date->format('d/m/Y'),
                    'time' => $sale->created_at->format('H:i'),
                    'cashier' => $sale->user->name,
                    'customer' => $sale->customer?->name ?? 'Umum',
                ],
                'items' => $sale->items->map(fn ($item) => [
                    'name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit?->name ?? $item->product->baseUnit?->name ?? 'pcs',
                    'price' => (float) $item->price,
                    'discount' => (float) $item->discount,
                    'subtotal' => (float) $item->subtotal,
                ]),
                'summary' => [
                    'subtotal' => (float) $sale->subtotal,
                    'discount' => (float) $sale->discount,
                    'tax' => (float) $sale->tax,
                    'total' => (float) $sale->total,
                    'paid_amount' => (float) $sale->paid_amount,
                    'change_amount' => (float) $sale->change_amount,
                ],
                'payments' => $sale->payments->map(fn ($p) => [
                    'method' => $p->paymentMethod?->name ?? 'Cash',
                    'amount' => (float) $p->amount,
                ]),
            ],
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $lastSale = Sale::whereDate('date', today())->orderByDesc('id')->first();
        $sequence = $lastSale ? ((int) substr($lastSale->invoice_number, -4)) + 1 : 1;

        return "INV-{$date}-".str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
