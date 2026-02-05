<?php

namespace Database\Seeders;

use App\Enums\StockMovementType;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $batches = ProductBatch::with('product')->get();

        foreach ($batches as $batch) {
            // Initial purchase movement
            StockMovement::firstOrCreate(
                [
                    'product_batch_id' => $batch->id,
                    'type' => StockMovementType::Purchase,
                    'reference_type' => null,
                    'reference_id' => null,
                ],
                [
                    'product_batch_id' => $batch->id,
                    'type' => StockMovementType::Purchase,
                    'quantity' => $batch->initial_stock,
                    'stock_before' => 0,
                    'stock_after' => $batch->initial_stock,
                    'notes' => 'Stok awal dari pembelian',
                    'user_id' => $user?->id,
                ]
            );

            // Some random sales for active batches
            if ($batch->stock > 20) {
                $saleQty = rand(5, 15);
                StockMovement::create([
                    'product_batch_id' => $batch->id,
                    'type' => StockMovementType::Sale,
                    'quantity' => $saleQty,
                    'stock_before' => $batch->stock,
                    'stock_after' => $batch->stock - $saleQty,
                    'notes' => 'Penjualan reguler',
                    'user_id' => $user?->id,
                ]);
            }

            // Some adjustments for random batches
            if (rand(0, 10) > 7) {
                $adjustQty = rand(1, 5);
                $isIn = rand(0, 1) === 1;
                StockMovement::create([
                    'product_batch_id' => $batch->id,
                    'type' => $isIn ? StockMovementType::AdjustmentIn : StockMovementType::AdjustmentOut,
                    'quantity' => $adjustQty,
                    'stock_before' => $batch->stock,
                    'stock_after' => $isIn ? $batch->stock + $adjustQty : $batch->stock - $adjustQty,
                    'notes' => $isIn ? 'Penyesuaian stok masuk dari stock opname' : 'Penyesuaian stok keluar dari stock opname',
                    'user_id' => $user?->id,
                ]);
            }
        }
    }
}
