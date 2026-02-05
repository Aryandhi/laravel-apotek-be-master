<?php

namespace App\Http\Controllers\Pos;

use App\Enums\SaleStatus;
use App\Enums\ShiftStatus;
use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\Category;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\StockMovement;
use App\Services\StockAllocationService;
use App\Services\XenditService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $currentShift = CashierShift::where('user_id', auth()->id())
            ->where('status', ShiftStatus::Open)
            ->first();

        if (! $currentShift) {
            return redirect()->route('pos.shift.create')
                ->with('error', 'Silakan buka shift terlebih dahulu');
        }

        $paymentMethods = PaymentMethod::active()->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        // Load products directly for initial render
        $products = Product::query()
            ->with(['category', 'baseUnit', 'activeBatches' => fn ($q) => $q->where('stock', '>', 0)->fefo()])
            ->active()
            ->whereHas('activeBatches', fn ($q) => $q->where('stock', '>', 0))
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->map(function ($product) {
                $firstBatch = $product->activeBatches->first();

                return [
                    'id' => $product->id,
                    'code' => $product->code,
                    'barcode' => $product->barcode,
                    'name' => $product->name,
                    'generic_name' => $product->generic_name,
                    'category' => $product->category?->name,
                    'unit' => $product->baseUnit?->name,
                    'selling_price' => $firstBatch?->selling_price ?? $product->selling_price,
                    'total_stock' => $product->activeBatches->sum('stock'),
                    'requires_prescription' => $product->requires_prescription,
                    'image_url' => $product->image_url,
                    'batch' => $firstBatch ? [
                        'id' => $firstBatch->id,
                        'batch_number' => $firstBatch->batch_number,
                        'expired_date' => $firstBatch->expired_date->format('d M Y'),
                        'stock' => $firstBatch->stock,
                        'selling_price' => $firstBatch->selling_price,
                    ] : null,
                ];
            });

        $xenditEnabled = app(XenditService::class)->isEnabled();

        return view('pos.transactions.create', compact('paymentMethods', 'customers', 'currentShift', 'categories', 'products', 'xenditEnabled'));
    }

    public function getProducts(Request $request): JsonResponse
    {
        Log::info('POS getProducts called', [
            'user_id' => auth()->id(),
            'search' => $request->search,
            'category_id' => $request->category_id,
        ]);

        try {
            $query = Product::query()
                ->with(['category', 'baseUnit', 'activeBatches' => fn ($q) => $q->where('stock', '>', 0)->fefo()])
                ->active()
                ->whereHas('activeBatches', fn ($q) => $q->where('stock', '>', 0));

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('generic_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            $products = $query->orderBy('name')->limit(50)->get();

            Log::info('POS getProducts success', ['count' => $products->count()]);

            return response()->json([
                'data' => $products->map(function ($product) {
                    $firstBatch = $product->activeBatches->first();

                    return [
                        'id' => $product->id,
                        'code' => $product->code,
                        'barcode' => $product->barcode,
                        'name' => $product->name,
                        'generic_name' => $product->generic_name,
                        'category' => $product->category?->name,
                        'unit' => $product->baseUnit?->name,
                        'selling_price' => $firstBatch?->selling_price ?? $product->selling_price,
                        'total_stock' => $product->activeBatches->sum('stock'),
                        'requires_prescription' => $product->requires_prescription,
                        'image_url' => $product->image_url,
                        'batch' => $firstBatch ? [
                            'id' => $firstBatch->id,
                            'batch_number' => $firstBatch->batch_number,
                            'expired_date' => $firstBatch->expired_date->format('d M Y'),
                            'stock' => $firstBatch->stock,
                            'selling_price' => $firstBatch->selling_price,
                        ] : null,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            Log::error('POS getProducts error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan saat memuat produk',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        Log::channel('single')->info('=== POS STORE START ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'user_email' => auth()->user()?->email,
            'ip' => $request->ip(),
            'request_data' => $request->all(),
        ]);

        try {
            Log::channel('single')->info('POS: Starting validation');

            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.batch_id' => 'nullable|exists:product_batches,id', // Now optional - will auto-allocate
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
                'customer_id' => 'nullable|exists:customers,id',
                'discount' => 'nullable|numeric|min:0',
                'payments' => 'required|array|min:1',
                'payments.*.payment_method_id' => 'required|exists:payment_methods,id',
                'payments.*.amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500',
            ]);

            Log::channel('single')->info('POS: Validation passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('single')->error('POS: Validation FAILED', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::channel('single')->error('POS: Validation exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }

        try {
            Log::channel('single')->info('POS: Checking shift');

            $currentShift = CashierShift::where('user_id', auth()->id())
                ->where('status', ShiftStatus::Open)
                ->first();

            Log::channel('single')->info('POS: Shift check result', [
                'shift_found' => (bool) $currentShift,
                'shift_id' => $currentShift?->id,
                'shift_status' => $currentShift?->status?->value ?? 'N/A',
            ]);

            if (! $currentShift) {
                Log::channel('single')->warning('POS: No active shift - returning error');

                return response()->json(['error' => 'Tidak ada shift aktif'], 400);
            }
        } catch (\Exception $e) {
            Log::channel('single')->error('POS: Shift check exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }

        try {
            Log::channel('single')->info('POS: Starting DB transaction');
            DB::beginTransaction();

            // Use StockAllocationService for stock validation
            $stockService = app(StockAllocationService::class);

            // Pre-validate all items stock availability
            Log::channel('single')->info('POS: Checking stock availability');
            foreach ($request->items as $index => $item) {
                Log::channel('single')->info("POS: Checking item {$index}", $item);

                $validation = $stockService->validateStock($item['product_id'], $item['quantity']);

                if (! $validation['available']) {
                    Log::channel('single')->warning('POS: Insufficient stock', [
                        'product_id' => $item['product_id'],
                        'requested' => $item['quantity'],
                        'available' => $validation['total_stock'],
                    ]);

                    $product = Product::find($item['product_id']);

                    return response()->json([
                        'error' => "Stok tidak cukup untuk {$product->name}. Dibutuhkan: {$item['quantity']}, Tersedia: {$validation['total_stock']}",
                    ], 400);
                }
            }

            // Calculate totals
            Log::channel('single')->info('POS: Calculating totals');
            $subtotal = 0;
            foreach ($request->items as $item) {
                $itemSubtotal = ($item['price'] * $item['quantity']) - ($item['discount'] ?? 0);
                $subtotal += $itemSubtotal;
            }

            $discount = $request->discount ?? 0;
            $total = $subtotal - $discount;

            // Calculate payment totals
            $paidAmount = collect($request->payments)->sum('amount');
            $changeAmount = max(0, $paidAmount - $total);

            Log::channel('single')->info('POS: Totals calculated', [
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
            ]);

            // Check if prescription items exist
            $hasPrescription = false;
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                if ($product && $product->requires_prescription) {
                    $hasPrescription = true;
                    break;
                }
            }

            // Generate invoice number
            Log::channel('single')->info('POS: Generating invoice number');
            $invoiceNumber = $this->generateInvoiceNumber();
            Log::channel('single')->info('POS: Invoice number generated', ['invoice' => $invoiceNumber]);

            // Create sale
            Log::channel('single')->info('POS: Creating sale record');
            $saleData = [
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id,
                'is_prescription' => $hasPrescription,
                'date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => 0,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'status' => SaleStatus::Completed,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
                'shift_id' => $currentShift->id,
            ];
            Log::channel('single')->info('POS: Sale data prepared', $saleData);

            $sale = Sale::create($saleData);
            Log::channel('single')->info('POS: Sale created', ['sale_id' => $sale->id]);

            // Create sale items and update stock using multi-batch allocation
            Log::channel('single')->info('POS: Creating sale items with multi-batch allocation');
            foreach ($request->items as $index => $item) {
                $product = Product::find($item['product_id']);
                $itemDiscount = $item['discount'] ?? 0;
                $preferredBatchId = $item['batch_id'] ?? null;

                Log::channel('single')->info("POS: Allocating stock for item {$index}", [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'preferred_batch_id' => $preferredBatchId,
                ]);

                // Allocate stock from multiple batches if needed
                $allocation = $stockService->allocateStock(
                    $item['product_id'],
                    $item['quantity'],
                    $preferredBatchId
                );

                if (! $allocation['success']) {
                    throw new \Exception($allocation['message']);
                }

                Log::channel('single')->info('POS: Stock allocated from '.count($allocation['allocations']).' batch(es)');

                // Create sale items for each batch allocation
                foreach ($allocation['allocations'] as $allocIndex => $alloc) {
                    $batch = ProductBatch::find($alloc['batch_id']);
                    $allocDiscount = $itemDiscount * $alloc['quantity'] / $item['quantity'];
                    $allocSubtotal = ($item['price'] * $alloc['quantity']) - $allocDiscount;

                    Log::channel('single')->info("POS: Creating sale item {$index}.{$allocIndex}", [
                        'batch_id' => $alloc['batch_id'],
                        'batch_number' => $alloc['batch_number'],
                        'quantity' => $alloc['quantity'],
                    ]);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'product_batch_id' => $alloc['batch_id'],
                        'quantity' => $alloc['quantity'],
                        'unit_id' => $product->base_unit_id,
                        'price' => $item['price'],
                        'discount' => $allocDiscount,
                        'subtotal' => $allocSubtotal,
                        'is_prescription_item' => $product->requires_prescription,
                    ]);

                    // Track stock before update
                    $stockBefore = $batch->stock;

                    // Update batch stock
                    $batch->decrement('stock', $alloc['quantity']);
                    $batch->refresh();

                    $stockAfter = $batch->stock;

                    Log::channel('single')->info("POS: Stock updated for batch {$batch->id}", [
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                    ]);

                    // Create stock movement with before/after tracking
                    StockMovement::create([
                        'product_batch_id' => $alloc['batch_id'],
                        'type' => 'sale',
                        'quantity' => -$alloc['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'notes' => "Penjualan #{$invoiceNumber}",
                        'user_id' => auth()->id(),
                    ]);

                    Log::channel('single')->info("POS: Stock movement created for batch {$batch->batch_number}");
                }
            }

            // Create payments
            Log::channel('single')->info('POS: Creating payments');
            foreach ($request->payments as $index => $payment) {
                Log::channel('single')->info("POS: Creating payment {$index}", $payment);

                SalePayment::create([
                    'sale_id' => $sale->id,
                    'payment_method_id' => $payment['payment_method_id'],
                    'amount' => $payment['amount'],
                    'reference_number' => $payment['reference_number'] ?? null,
                ]);

                Log::channel('single')->info("POS: Payment {$index} created");
            }

            Log::channel('single')->info('POS: Committing transaction');
            DB::commit();
            Log::channel('single')->info('=== POS STORE SUCCESS ===', ['sale_id' => $sale->id]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil',
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                    'total' => $sale->total,
                    'paid_amount' => $sale->paid_amount,
                    'change_amount' => $sale->change_amount,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::channel('single')->error('=== POS STORE FAILED ===', [
                'message' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'error' => 'Terjadi kesalahan: '.$e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
            ], 500);
        }
    }

    public function show(Sale $sale): JsonResponse
    {
        $sale->load(['customer', 'items.product', 'items.productBatch', 'items.unit', 'payments.paymentMethod', 'user']);

        return response()->json([
            'id' => $sale->id,
            'invoice_number' => $sale->invoice_number,
            'customer' => $sale->customer?->name,
            'date' => $sale->date->format('d M Y H:i'),
            'subtotal' => $sale->subtotal,
            'discount' => $sale->discount,
            'total' => $sale->total,
            'paid_amount' => $sale->paid_amount,
            'change_amount' => $sale->change_amount,
            'status' => $sale->status->label(),
            'is_prescription' => $sale->is_prescription,
            'notes' => $sale->notes,
            'cashier' => $sale->user?->name,
            'items' => $sale->items->map(fn ($item) => [
                'product_name' => $item->product?->name,
                'batch_number' => $item->productBatch?->batch_number,
                'quantity' => $item->quantity,
                'unit' => $item->unit?->name,
                'price' => $item->price,
                'discount' => $item->discount,
                'subtotal' => $item->subtotal,
            ]),
            'payments' => $sale->payments->map(fn ($payment) => [
                'method' => $payment->paymentMethod?->name,
                'amount' => $payment->amount,
                'reference' => $payment->reference_number,
            ]),
        ]);
    }

    public function index(): View
    {
        return view('pos.transactions.index');
    }

    public function history(Request $request): JsonResponse
    {
        $currentShift = CashierShift::where('user_id', auth()->id())
            ->where('status', ShiftStatus::Open)
            ->first();

        $query = Sale::query()
            ->with(['customer', 'user'])
            ->where('user_id', auth()->id());

        if ($currentShift) {
            $query->where('shift_id', $currentShift->id);
        } else {
            $query->whereDate('date', today());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $sales->map(fn ($sale) => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'customer' => $sale->customer?->name ?? 'Umum',
                'total' => $sale->total,
                'status' => $sale->status->label(),
                'status_color' => $sale->status->color(),
                'is_prescription' => $sale->is_prescription,
                'time' => $sale->created_at->format('H:i'),
            ]),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'total' => $sales->total(),
            ],
        ]);
    }

    public function getProductBatches(Product $product): JsonResponse
    {
        $batches = $product->activeBatches()
            ->where('stock', '>', 0)
            ->fefo()
            ->get();

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'requires_prescription' => $product->requires_prescription,
                'selling_price' => $product->selling_price,
            ],
            'batches' => $batches->map(fn ($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expired_date' => $batch->expired_date->format('d M Y'),
                'days_until_expired' => $batch->daysUntilExpired(),
                'stock' => $batch->stock,
                'selling_price' => $batch->selling_price,
                'is_near_expired' => $batch->isNearExpired(),
            ]),
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastSale = Sale::whereDate('date', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastSale ? intval(substr($lastSale->invoice_number, -4)) + 1 : 1;

        return sprintf('%s%s%04d', $prefix, $date, $sequence);
    }
}
