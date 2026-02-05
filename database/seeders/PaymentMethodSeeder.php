<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $methods = [
            ['name' => 'Tunai', 'code' => 'CASH', 'is_cash' => true, 'is_active' => true],
            ['name' => 'Debit BCA', 'code' => 'DEBIT_BCA', 'is_cash' => false, 'is_active' => true],
            ['name' => 'Debit Mandiri', 'code' => 'DEBIT_MANDIRI', 'is_cash' => false, 'is_active' => true],
            ['name' => 'Debit BRI', 'code' => 'DEBIT_BRI', 'is_cash' => false, 'is_active' => true],
            ['name' => 'Debit BNI', 'code' => 'DEBIT_BNI', 'is_cash' => false, 'is_active' => true],
            ['name' => 'QRIS', 'code' => 'QRIS', 'is_cash' => false, 'is_active' => true],
            ['name' => 'GoPay', 'code' => 'GOPAY', 'is_cash' => false, 'is_active' => true],
            ['name' => 'OVO', 'code' => 'OVO', 'is_cash' => false, 'is_active' => true],
            ['name' => 'DANA', 'code' => 'DANA', 'is_cash' => false, 'is_active' => true],
            ['name' => 'ShopeePay', 'code' => 'SHOPEEPAY', 'is_cash' => false, 'is_active' => true],
            ['name' => 'LinkAja', 'code' => 'LINKAJA', 'is_cash' => false, 'is_active' => true],
            ['name' => 'Transfer Bank', 'code' => 'TRANSFER', 'is_cash' => false, 'is_active' => true],
            ['name' => 'Kredit', 'code' => 'CREDIT', 'is_cash' => false, 'is_active' => true],
        ];

        foreach ($methods as $method) {
            PaymentMethod::firstOrCreate(
                ['code' => $method['code']],
                $method
            );
        }
    }
}
