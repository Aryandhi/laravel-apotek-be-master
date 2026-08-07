<?php

namespace App\Services;

use App\Enums\BatchStatus;
use App\Models\Product;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductBatchImportService
{
    public function validateFile(string $filePath): array
    {
        $rows = $this->loadRows($filePath);
        $errors = [];
        $validRows = [];
        $seenBatchNumbers = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;
            $productName = $this->normalizeValue($row['product'] ?? $row['produk'] ?? $row['product_name'] ?? null);
            $batchNumber = $this->normalizeValue($row['batch_number'] ?? $row['nomor_batch'] ?? null);
            $purchasePrice = $this->normalizeDecimal($row['purchase_price'] ?? null, 0);
            $marginPercentage = $this->normalizeDecimal($row['margin_percentage'] ?? null, 0);
            $sellingPriceInput = $this->normalizeDecimal($row['selling_price'] ?? null, null);
            $stock = $this->normalizeInteger($row['stock'] ?? null, 0);
            $initialStock = $this->normalizeInteger($row['initial_stock'] ?? null, 0);
            $statusValue = $this->normalizeValue($row['status'] ?? null);

            if ($productName === null) {
                $errors[] = "Baris {$lineNumber}: kolom 'product' wajib diisi.";

                continue;
            }

            if ($batchNumber === null) {
                $errors[] = "Baris {$lineNumber}: kolom 'batch_number' wajib diisi.";

                continue;
            }

            $product = $this->resolveProduct($productName);

            if (! $product) {
                $errors[] = "Baris {$lineNumber}: produk '{$productName}' tidak ditemukan.";

                continue;
            }

            $expiredDate = $this->normalizeDate($row['expired_date'] ?? null);

            if ($expiredDate === null) {
                $errors[] = "Baris {$lineNumber}: kolom 'expired_date' wajib diisi dan harus berupa tanggal valid.";

                continue;
            }

            $normalizedStatus = $this->normalizeStatus($statusValue);

            if ($normalizedStatus === null) {
                $errors[] = "Baris {$lineNumber}: status '{$statusValue}' tidak valid.";

                continue;
            }

            if (isset($seenBatchNumbers[$batchNumber])) {
                $errors[] = "Baris {$lineNumber}: nomor batch '{$batchNumber}' duplikat dengan baris {$seenBatchNumbers[$batchNumber]} pada file yang sama.";

                continue;
            }

            $existingBatch = ProductBatch::query()
                ->where('batch_number', $batchNumber)
                ->exists();

            if ($existingBatch) {
                $errors[] = "Baris {$lineNumber}: nomor batch '{$batchNumber}' sudah digunakan pada data batch produk lain.";

                continue;
            }

            $seenBatchNumbers[$batchNumber] = $lineNumber;

            $sellingPrice = $sellingPriceInput ?? round($purchasePrice * (1 + ($marginPercentage / 100)), 2);

            $validRows[] = [
                'product_id' => $product->id,
                'batch_number' => $batchNumber,
                'expired_date' => $expiredDate,
                'purchase_price' => round($purchasePrice, 2),
                'margin_percentage' => round($marginPercentage, 2),
                'selling_price' => round($sellingPrice, 2),
                'stock' => $stock,
                'initial_stock' => $initialStock,
                'status' => $normalizedStatus,
            ];
        }

        return [
            'rows' => $validRows,
            'errors' => $errors,
        ];
    }

    public function importRows(array $rows): int
    {
        $created = 0;

        foreach ($rows as $row) {
            ProductBatch::create($row);
            $created++;
        }

        return $created;
    }

    protected function loadRows(string $filePath): Collection
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $allRows = $worksheet->toArray(null, true, true, true);

        if (empty($allRows) || count($allRows) < 2) {
            return collect([]);
        }

        $headerRow = array_shift($allRows);
        $headers = [];

        foreach ($headerRow as $column => $value) {
            if ($value === null) {
                continue;
            }

            $normalizedHeader = $this->normalizeHeader((string) $value);

            if ($normalizedHeader !== '') {
                $headers[$column] = $normalizedHeader;
            }
        }

        $rows = collect();

        foreach ($allRows as $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $normalizedRow = [];

            foreach ($headers as $column => $field) {
                $normalizedRow[$field] = $row[$column] ?? null;
            }

            $rows->push($normalizedRow);
        }

        return $rows;
    }

    protected function normalizeHeader(string $header): string
    {
        return Str::of($header)
            ->trim()
            ->lower()
            ->replaceMatches('/[\s\-\.]+/', '_')
            ->replace('\\', '_')
            ->replace('/', '_')
            ->replace('(', '')
            ->replace(')', '')
            ->__toString();
    }

    protected function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function normalizeInteger(mixed $value, ?int $default = null): ?int
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $value = preg_replace('/[^0-9-]+/', '', (string) $value);

        if ($value === '') {
            return $default;
        }

        return (int) $value;
    }

    protected function normalizeDecimal(mixed $value, ?float $default = null): ?float
    {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $value = preg_replace('/[^0-9.-]+/', '', (string) $value);

        if ($value === '' || $value === '-') {
            return $default;
        }

        return (float) $value;
    }

    protected function normalizeDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    protected function normalizeStatus(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return BatchStatus::Active->value;
        }

        $normalized = Str::of($status)->trim()->lower()->replaceMatches('/\s+/', '_')->__toString();

        return match ($normalized) {
            'active', 'aktif' => BatchStatus::Active->value,
            'near_expired', 'nearexpired', 'near_expired', 'mendekati_kadaluarsa' => BatchStatus::NearExpired->value,
            'expired', 'kadaluarsa' => BatchStatus::Expired->value,
            'returned', 'dikembalikan' => BatchStatus::Returned->value,
            'damaged', 'rusak' => BatchStatus::Damaged->value,
            default => null,
        };
    }

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function resolveProduct(string $productName): ?Product
    {
        $value = trim($productName);

        return Product::query()
            ->where(function ($query) use ($value): void {
                $query->whereRaw('LOWER(name) = ?', [Str::lower($value)])
                    ->orWhereRaw('LOWER(code) = ?', [Str::lower($value)])
                    ->orWhereRaw('LOWER(barcode) = ?', [Str::lower($value)]);
            })
            ->first();
    }
}
