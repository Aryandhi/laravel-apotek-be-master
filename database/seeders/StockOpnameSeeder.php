<?php

namespace Database\Seeders;

use App\Enums\StockOpnameStatus;
use App\Models\ProductBatch;
use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class StockOpnameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $approver = User::where('email', 'owner@apotek.com')->first() ?? $user;

        // Create a completed stock opname from last month
        $completedOpname = StockOpname::firstOrCreate(
            ['code' => 'SO-' . now()->subMonth()->format('Ymd') . '-001'],
            [
                'code' => 'SO-' . now()->subMonth()->format('Ymd') . '-001',
                'date' => now()->subMonth(),
                'status' => StockOpnameStatus::Approved,
                'notes' => 'Stock opname bulanan',
                'user_id' => $user?->id,
                'approved_by' => $approver?->id,
                'approved_at' => now()->subMonth()->addDays(2),
            ]
        );

        // Add items for completed opname
        $batches = ProductBatch::with('product')->take(10)->get();
        foreach ($batches as $batch) {
            $systemStock = $batch->stock;
            $physicalStock = $systemStock + rand(-3, 3);
            StockOpnameItem::firstOrCreate(
                [
                    'stock_opname_id' => $completedOpname->id,
                    'product_batch_id' => $batch->id,
                ],
                [
                    'stock_opname_id' => $completedOpname->id,
                    'product_id' => $batch->product_id,
                    'product_batch_id' => $batch->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $physicalStock - $systemStock,
                    'notes' => $physicalStock !== $systemStock ? 'Selisih ditemukan' : null,
                ]
            );
        }

        // Create a pending approval stock opname
        $pendingOpname = StockOpname::firstOrCreate(
            ['code' => 'SO-' . now()->format('Ymd') . '-001'],
            [
                'code' => 'SO-' . now()->format('Ymd') . '-001',
                'date' => now(),
                'status' => StockOpnameStatus::PendingApproval,
                'notes' => 'Stock opname menunggu persetujuan',
                'user_id' => $user?->id,
            ]
        );

        // Add items for pending opname
        $otherBatches = ProductBatch::with('product')->skip(10)->take(8)->get();
        foreach ($otherBatches as $batch) {
            $systemStock = $batch->stock;
            $physicalStock = $systemStock + rand(-2, 2);
            StockOpnameItem::firstOrCreate(
                [
                    'stock_opname_id' => $pendingOpname->id,
                    'product_batch_id' => $batch->id,
                ],
                [
                    'stock_opname_id' => $pendingOpname->id,
                    'product_id' => $batch->product_id,
                    'product_batch_id' => $batch->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $physicalStock - $systemStock,
                    'notes' => $physicalStock !== $systemStock ? 'Perlu verifikasi' : null,
                ]
            );
        }

        // Create a draft stock opname
        StockOpname::firstOrCreate(
            ['code' => 'SO-' . now()->format('Ymd') . '-002'],
            [
                'code' => 'SO-' . now()->format('Ymd') . '-002',
                'date' => now(),
                'status' => StockOpnameStatus::Draft,
                'notes' => 'Stock opname draft',
                'user_id' => $user?->id,
            ]
        );
    }
}
