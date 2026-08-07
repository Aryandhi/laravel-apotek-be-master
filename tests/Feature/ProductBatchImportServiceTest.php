<?php

namespace Tests\Feature;

use App\Enums\BatchStatus;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Unit;
use App\Services\ProductBatchImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class ProductBatchImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_validates_and_imports_product_batches_from_excel_rows(): void
    {
        $categoryType = CategoryType::create([
            'name' => 'Obat Bebas',
            'code' => 'obat_bebas',
            'requires_prescription' => false,
            'is_narcotic' => false,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Obat',
            'type' => 'obat_bebas',
            'category_type_id' => $categoryType->id,
        ]);

        $unit = Unit::create(['name' => 'Strip', 'code' => 'STR']);

        $product = Product::create([
            'code' => 'PRD001',
            'barcode' => '1234567890123',
            'name' => 'Paracetamol 500mg',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'batch-import-').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['product', 'batch_number', 'expired_date', 'purchase_price', 'margin_percentage', 'selling_price', 'stock', 'initial_stock', 'status'],
            [$product->name, 'BATCH-001', '2026-12-31', 3000, 30, 3900, 50, 50, 'active'],
        ], 1);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $service = new ProductBatchImportService;
        $result = $service->validateFile($filePath);

        $this->assertSame([], $result['errors']);
        $this->assertCount(1, $result['rows']);
        $this->assertSame($product->id, $result['rows'][0]['product_id']);
        $this->assertSame('BATCH-001', $result['rows'][0]['batch_number']);

        $created = $service->importRows($result['rows']);

        $this->assertSame(1, $created);
        $this->assertDatabaseCount('product_batches', 1);

        $batch = ProductBatch::first();
        $this->assertNotNull($batch);
        $this->assertSame($product->id, $batch->product_id);
        $this->assertSame('BATCH-001', $batch->batch_number);
        $this->assertSame(3000.00, (float) $batch->purchase_price);
        $this->assertSame(30.00, (float) $batch->margin_percentage);
        $this->assertSame(3900.00, (float) $batch->selling_price);
        $this->assertSame(50, $batch->stock);
        $this->assertSame(50, $batch->initial_stock);
        $this->assertSame(BatchStatus::Active, $batch->status);
    }

    public function test_it_rejects_batch_number_already_used_by_another_product(): void
    {
        $categoryType = CategoryType::create([
            'name' => 'Obat Bebas',
            'code' => 'obat_bebas',
            'requires_prescription' => false,
            'is_narcotic' => false,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Obat',
            'type' => 'obat_bebas',
            'category_type_id' => $categoryType->id,
        ]);

        $unit = Unit::create(['name' => 'Strip', 'code' => 'STR']);

        $existingProduct = Product::create([
            'code' => 'PRD001',
            'barcode' => '1234567890123',
            'name' => 'Hufagripp',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        ProductBatch::create([
            'product_id' => $existingProduct->id,
            'batch_number' => 'BATCH-001',
            'expired_date' => '2026-12-31',
            'purchase_price' => 3000,
            'margin_percentage' => 30,
            'selling_price' => 3900,
            'stock' => 50,
            'initial_stock' => 50,
            'status' => BatchStatus::Active,
        ]);

        $otherProduct = Product::create([
            'code' => 'PRD002',
            'barcode' => '9876543210123',
            'name' => 'Sutra',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'batch-import-').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['product', 'batch_number', 'expired_date', 'purchase_price', 'margin_percentage', 'selling_price', 'stock', 'initial_stock', 'status'],
            [$otherProduct->name, 'BATCH-001', '2026-12-31', 3000, 30, 3900, 50, 50, 'active'],
        ], 1);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $service = new ProductBatchImportService;
        $result = $service->validateFile($filePath);

        $this->assertCount(0, $result['rows']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('sudah digunakan', $result['errors'][0]);
    }

    public function test_it_rejects_duplicate_batch_number_within_same_file(): void
    {
        $categoryType = CategoryType::create([
            'name' => 'Obat Bebas',
            'code' => 'obat_bebas',
            'requires_prescription' => false,
            'is_narcotic' => false,
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Obat',
            'type' => 'obat_bebas',
            'category_type_id' => $categoryType->id,
        ]);

        $unit = Unit::create(['name' => 'Strip', 'code' => 'STR']);

        $productA = Product::create([
            'code' => 'PRD001',
            'barcode' => '1234567890123',
            'name' => 'Hufagripp',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $productB = Product::create([
            'code' => 'PRD002',
            'barcode' => '9876543210123',
            'name' => 'Sutra',
            'category_id' => $category->id,
            'base_unit_id' => $unit->id,
            'purchase_price' => 3000,
            'selling_price' => 3900,
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $filePath = tempnam(sys_get_temp_dir(), 'batch-import-').'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['product', 'batch_number', 'expired_date', 'purchase_price', 'margin_percentage', 'selling_price', 'stock', 'initial_stock', 'status'],
            [$productA->name, 'BATCH-001', '2026-12-31', 3000, 30, 3900, 50, 50, 'active'],
            [$productB->name, 'BATCH-001', '2026-12-31', 3000, 30, 3900, 50, 50, 'active'],
        ], 1);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        $service = new ProductBatchImportService;
        $result = $service->validateFile($filePath);

        $this->assertCount(1, $result['rows']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('duplikat', $result['errors'][0]);
    }
}
