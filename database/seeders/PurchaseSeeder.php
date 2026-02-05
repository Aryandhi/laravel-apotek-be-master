<?php

namespace Database\Seeders;

use App\Enums\PurchaseStatus;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $suppliers = Supplier::all();
        $products = Product::with('baseUnit')->get();
        $cashPayment = PaymentMethod::where('code', 'CASH')->first();
        $transferPayment = PaymentMethod::where('code', 'TRANSFER')->first();

        // Purchase 1: Completed and fully paid
        $purchase1 = Purchase::firstOrCreate(
            ['invoice_number' => 'PO-' . now()->subDays(10)->format('Ymd') . '-001'],
            [
                'invoice_number' => 'PO-' . now()->subDays(10)->format('Ymd') . '-001',
                'supplier_id' => $suppliers->first()?->id,
                'date' => now()->subDays(10),
                'due_date' => now()->subDays(10)->addDays(30),
                'status' => PurchaseStatus::Received,
                'subtotal' => 2500000,
                'discount' => 50000,
                'tax' => 0,
                'total' => 2450000,
                'paid_amount' => 2450000,
                'notes' => 'Pembelian rutin bulanan',
                'user_id' => $user?->id,
            ]
        );

        // Items for purchase 1
        $this->createPurchaseItems($purchase1, $products->take(5));

        // Payment for purchase 1
        PurchasePayment::firstOrCreate(
            ['purchase_id' => $purchase1->id, 'payment_date' => now()->subDays(10)],
            [
                'purchase_id' => $purchase1->id,
                'amount' => 2450000,
                'payment_method_id' => $transferPayment?->id,
                'payment_date' => now()->subDays(10),
                'reference_number' => 'TRF-001',
                'notes' => 'Pembayaran lunas',
                'user_id' => $user?->id,
            ]
        );

        // Purchase 2: Received but partial payment
        $purchase2 = Purchase::firstOrCreate(
            ['invoice_number' => 'PO-' . now()->subDays(5)->format('Ymd') . '-001'],
            [
                'invoice_number' => 'PO-' . now()->subDays(5)->format('Ymd') . '-001',
                'supplier_id' => $suppliers->skip(1)->first()?->id ?? $suppliers->first()?->id,
                'date' => now()->subDays(5),
                'due_date' => now()->addDays(25),
                'status' => PurchaseStatus::Received,
                'subtotal' => 3500000,
                'discount' => 0,
                'tax' => 0,
                'total' => 3500000,
                'paid_amount' => 1500000,
                'notes' => 'Pembelian dengan pembayaran bertahap',
                'user_id' => $user?->id,
            ]
        );

        // Items for purchase 2
        $this->createPurchaseItems($purchase2, $products->skip(5)->take(5));

        // Partial payment for purchase 2
        PurchasePayment::firstOrCreate(
            ['purchase_id' => $purchase2->id, 'payment_date' => now()->subDays(5)],
            [
                'purchase_id' => $purchase2->id,
                'amount' => 1500000,
                'payment_method_id' => $cashPayment?->id,
                'payment_date' => now()->subDays(5),
                'reference_number' => null,
                'notes' => 'DP 50%',
                'user_id' => $user?->id,
            ]
        );

        // Purchase 3: Draft (not yet ordered)
        Purchase::firstOrCreate(
            ['invoice_number' => 'PO-' . now()->format('Ymd') . '-001'],
            [
                'invoice_number' => 'PO-' . now()->format('Ymd') . '-001',
                'supplier_id' => $suppliers->first()?->id,
                'date' => now(),
                'due_date' => now()->addDays(30),
                'status' => PurchaseStatus::Draft,
                'subtotal' => 1800000,
                'discount' => 0,
                'tax' => 0,
                'total' => 1800000,
                'paid_amount' => 0,
                'notes' => 'Draft pembelian',
                'user_id' => $user?->id,
            ]
        );

        // Purchase 4: Ordered (waiting for delivery)
        $purchase4 = Purchase::firstOrCreate(
            ['invoice_number' => 'PO-' . now()->subDays(2)->format('Ymd') . '-001'],
            [
                'invoice_number' => 'PO-' . now()->subDays(2)->format('Ymd') . '-001',
                'supplier_id' => $suppliers->skip(2)->first()?->id ?? $suppliers->first()?->id,
                'date' => now()->subDays(2),
                'due_date' => now()->addDays(28),
                'status' => PurchaseStatus::Ordered,
                'subtotal' => 5000000,
                'discount' => 100000,
                'tax' => 0,
                'total' => 4900000,
                'paid_amount' => 0,
                'notes' => 'Menunggu pengiriman',
                'user_id' => $user?->id,
            ]
        );

        // Items for purchase 4
        $this->createPurchaseItems($purchase4, $products->skip(10)->take(5));
    }

    private function createPurchaseItems(Purchase $purchase, $products): void
    {
        foreach ($products as $product) {
            $quantity = rand(20, 100);
            $subtotal = $quantity * $product->purchase_price;

            PurchaseItem::firstOrCreate(
                [
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                ],
                [
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'batch_number' => 'BTH-PO-' . $purchase->id . '-' . $product->id,
                    'expired_date' => now()->addMonths(rand(12, 24)),
                    'quantity' => $quantity,
                    'unit_id' => $product->base_unit_id,
                    'purchase_price' => $product->purchase_price,
                    'selling_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                    'discount' => 0,
                    'total' => $subtotal,
                    'received_quantity' => $purchase->status === PurchaseStatus::Received ? $quantity : 0,
                ]
            );
        }
    }
}
