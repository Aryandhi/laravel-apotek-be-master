<?php

namespace Database\Seeders;

use App\Enums\SaleStatus;
use App\Models\CashierShift;
use App\Models\Customer;
use App\Models\Doctor;
use App\Models\PaymentMethod;
use App\Models\ProductBatch;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'kasir@apotek.com')->first() ?? User::first();
        $customers = Customer::all();
        $doctors = Doctor::all();
        $batches = ProductBatch::with('product')->where('stock', '>', 10)->get();
        $cashPayment = PaymentMethod::where('code', 'CASH')->first();
        $debitPayment = PaymentMethod::where('code', 'DEBIT')->first();
        $shift = CashierShift::latest()->first();

        // Sale 1: Regular sale with cash
        $sale1 = Sale::firstOrCreate(
            ['invoice_number' => 'INV-' . now()->subDays(3)->format('Ymd') . '-001'],
            [
                'invoice_number' => 'INV-' . now()->subDays(3)->format('Ymd') . '-001',
                'customer_id' => $customers->where('name', 'Umum')->first()?->id,
                'doctor_id' => null,
                'prescription_number' => null,
                'is_prescription' => false,
                'patient_name' => null,
                'patient_address' => null,
                'date' => now()->subDays(3),
                'subtotal' => 85000,
                'discount' => 0,
                'tax' => 0,
                'total' => 85000,
                'paid_amount' => 100000,
                'change_amount' => 15000,
                'status' => SaleStatus::Completed,
                'notes' => 'Penjualan tunai',
                'user_id' => $user?->id,
                'shift_id' => $shift?->id,
            ]
        );

        $this->createSaleItems($sale1, $batches->take(3));
        $this->createSalePayment($sale1, $cashPayment, 100000);

        // Sale 2: Prescription sale
        $sale2 = Sale::firstOrCreate(
            ['invoice_number' => 'INV-' . now()->subDays(2)->format('Ymd') . '-001'],
            [
                'invoice_number' => 'INV-' . now()->subDays(2)->format('Ymd') . '-001',
                'customer_id' => $customers->skip(1)->first()?->id,
                'doctor_id' => $doctors->first()?->id,
                'prescription_number' => 'R/' . now()->subDays(2)->format('Ymd') . '/001',
                'is_prescription' => true,
                'patient_name' => 'Budi Santoso',
                'patient_address' => 'Jl. Merdeka No. 10',
                'date' => now()->subDays(2),
                'subtotal' => 250000,
                'discount' => 25000,
                'tax' => 0,
                'total' => 225000,
                'paid_amount' => 225000,
                'change_amount' => 0,
                'status' => SaleStatus::Completed,
                'notes' => 'Penjualan dengan resep',
                'user_id' => $user?->id,
                'shift_id' => $shift?->id,
            ]
        );

        $this->createSaleItems($sale2, $batches->skip(3)->take(4), true);
        $this->createSalePayment($sale2, $debitPayment, 225000);

        // Sale 3: Regular sale with member
        $sale3 = Sale::firstOrCreate(
            ['invoice_number' => 'INV-' . now()->subDays(1)->format('Ymd') . '-001'],
            [
                'invoice_number' => 'INV-' . now()->subDays(1)->format('Ymd') . '-001',
                'customer_id' => $customers->skip(2)->first()?->id,
                'doctor_id' => null,
                'prescription_number' => null,
                'is_prescription' => false,
                'patient_name' => null,
                'patient_address' => null,
                'date' => now()->subDays(1),
                'subtotal' => 175000,
                'discount' => 17500,
                'tax' => 0,
                'total' => 157500,
                'paid_amount' => 160000,
                'change_amount' => 2500,
                'status' => SaleStatus::Completed,
                'notes' => 'Penjualan member - diskon 10%',
                'user_id' => $user?->id,
                'shift_id' => $shift?->id,
            ]
        );

        $this->createSaleItems($sale3, $batches->skip(7)->take(3));
        $this->createSalePayment($sale3, $cashPayment, 160000);

        // Sale 4: Today's sale
        $sale4 = Sale::firstOrCreate(
            ['invoice_number' => 'INV-' . now()->format('Ymd') . '-001'],
            [
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-001',
                'customer_id' => $customers->where('name', 'Umum')->first()?->id,
                'doctor_id' => null,
                'prescription_number' => null,
                'is_prescription' => false,
                'patient_name' => null,
                'patient_address' => null,
                'date' => now(),
                'subtotal' => 45000,
                'discount' => 0,
                'tax' => 0,
                'total' => 45000,
                'paid_amount' => 50000,
                'change_amount' => 5000,
                'status' => SaleStatus::Completed,
                'notes' => null,
                'user_id' => $user?->id,
                'shift_id' => $shift?->id,
            ]
        );

        $this->createSaleItems($sale4, $batches->skip(10)->take(2));
        $this->createSalePayment($sale4, $cashPayment, 50000);

        // Sale 5: Today's prescription sale
        $sale5 = Sale::firstOrCreate(
            ['invoice_number' => 'INV-' . now()->format('Ymd') . '-002'],
            [
                'invoice_number' => 'INV-' . now()->format('Ymd') . '-002',
                'customer_id' => $customers->skip(3)->first()?->id,
                'doctor_id' => $doctors->skip(1)->first()?->id ?? $doctors->first()?->id,
                'prescription_number' => 'R/' . now()->format('Ymd') . '/001',
                'is_prescription' => true,
                'patient_name' => 'Dewi Lestari',
                'patient_address' => 'Jl. Sudirman No. 25',
                'date' => now(),
                'subtotal' => 320000,
                'discount' => 0,
                'tax' => 0,
                'total' => 320000,
                'paid_amount' => 320000,
                'change_amount' => 0,
                'status' => SaleStatus::Completed,
                'notes' => 'Penjualan resep dokter',
                'user_id' => $user?->id,
                'shift_id' => $shift?->id,
            ]
        );

        $this->createSaleItems($sale5, $batches->skip(12)->take(5), true);
        $this->createSalePayment($sale5, $cashPayment, 320000);
    }

    private function createSaleItems(Sale $sale, $batches, bool $isPrescription = false): void
    {
        foreach ($batches as $batch) {
            $quantity = rand(1, 5);
            $price = $batch->selling_price;
            $subtotal = $quantity * $price;

            SaleItem::firstOrCreate(
                [
                    'sale_id' => $sale->id,
                    'product_batch_id' => $batch->id,
                ],
                [
                    'sale_id' => $sale->id,
                    'product_id' => $batch->product_id,
                    'product_batch_id' => $batch->id,
                    'quantity' => $quantity,
                    'unit_id' => $batch->product->base_unit_id,
                    'price' => $price,
                    'discount' => 0,
                    'subtotal' => $subtotal,
                    'is_prescription_item' => $isPrescription,
                    'notes' => null,
                ]
            );
        }
    }

    private function createSalePayment(Sale $sale, ?PaymentMethod $paymentMethod, float $amount): void
    {
        if (! $paymentMethod) {
            return;
        }

        SalePayment::firstOrCreate(
            [
                'sale_id' => $sale->id,
                'payment_method_id' => $paymentMethod->id,
            ],
            [
                'sale_id' => $sale->id,
                'payment_method_id' => $paymentMethod->id,
                'amount' => $amount,
                'reference_number' => $paymentMethod->code !== 'CASH' ? 'REF-' . $sale->id : null,
            ]
        );
    }
}
