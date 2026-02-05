<?php

namespace Database\Seeders;

use App\Enums\ShiftStatus;
use App\Models\CashierShift;
use App\Models\User;
use Illuminate\Database\Seeder;

class CashierShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cashier = User::where('email', 'kasir@apotek.com')->first() ?? User::first();

        // Closed shifts from previous days
        CashierShift::firstOrCreate(
            ['opening_time' => now()->subDays(3)->setTime(8, 0)],
            [
                'user_id' => $cashier?->id,
                'opening_time' => now()->subDays(3)->setTime(8, 0),
                'closing_time' => now()->subDays(3)->setTime(17, 0),
                'opening_cash' => 500000,
                'expected_cash' => 2850000,
                'actual_cash' => 2850000,
                'difference' => 0,
                'status' => ShiftStatus::Closed,
                'notes' => 'Shift pagi - tutup normal',
            ]
        );

        CashierShift::firstOrCreate(
            ['opening_time' => now()->subDays(2)->setTime(8, 0)],
            [
                'user_id' => $cashier?->id,
                'opening_time' => now()->subDays(2)->setTime(8, 0),
                'closing_time' => now()->subDays(2)->setTime(17, 0),
                'opening_cash' => 500000,
                'expected_cash' => 3200000,
                'actual_cash' => 3195000,
                'difference' => -5000,
                'status' => ShiftStatus::Closed,
                'notes' => 'Shift pagi - selisih kurang Rp 5.000',
            ]
        );

        CashierShift::firstOrCreate(
            ['opening_time' => now()->subDays(1)->setTime(8, 0)],
            [
                'user_id' => $cashier?->id,
                'opening_time' => now()->subDays(1)->setTime(8, 0),
                'closing_time' => now()->subDays(1)->setTime(17, 0),
                'opening_cash' => 500000,
                'expected_cash' => 4100000,
                'actual_cash' => 4100000,
                'difference' => 0,
                'status' => ShiftStatus::Closed,
                'notes' => 'Shift pagi - tutup normal',
            ]
        );

        // Current open shift
        CashierShift::firstOrCreate(
            ['opening_time' => now()->setTime(8, 0)],
            [
                'user_id' => $cashier?->id,
                'opening_time' => now()->setTime(8, 0),
                'closing_time' => null,
                'opening_cash' => 500000,
                'expected_cash' => 500000,
                'actual_cash' => null,
                'difference' => null,
                'status' => ShiftStatus::Open,
                'notes' => 'Shift berjalan',
            ]
        );
    }
}
