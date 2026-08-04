<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProductImportService
{
    public function validateFile(string $filePath): array
    {
        $rows = $this->loadRows($filePath);
        $errors = [];
        $validRows = [];
        $barcodeMap = [];

        foreach ($rows as $index => $row) {
            $lineNumber = $index + 2;
            $barcode = $this->normalizeValue($row['barcode'] ?? null);
            $name = $this->normalizeValue($row['name'] ?? null);
            $genericName = $this->normalizeValue($row['generic_name'] ?? null);
            $categoryName = $this->normalizeValue($row['category'] ?? null);
            $unitName = $this->normalizeValue($row['unit'] ?? null);
            $minStock = $this->normalizeInteger($row['min_stock'] ?? null, 10);
            $maxStock = $this->normalizeInteger($row['max_stock'] ?? null, null);
            $rackLocation = $this->normalizeValue($row['rack_location'] ?? null);
            $description = $this->normalizeValue($row['description'] ?? null);

            if ($name === null) {
                $errors[] = "Baris {$lineNumber}: kolom 'name' wajib diisi.";
                continue;
            }

            if ($categoryName === null) {
                $errors[] = "Baris {$lineNumber}: kolom 'category' wajib diisi.";
                continue;
            }

            if ($unitName === null) {
                $errors[] = "Baris {$lineNumber}: kolom 'unit' wajib diisi.";
                continue;
            }

            $category = Category::whereRaw('LOWER(name) = ?', [Str::lower($categoryName)])->first();

            if (! $category) {
                $errors[] = "Baris {$lineNumber}: kategori '{$categoryName}' tidak ditemukan.";
                continue;
            }

            $unit = Unit::whereRaw('LOWER(name) = ?', [Str::lower($unitName)])->first();

            if (! $unit) {
                $errors[] = "Baris {$lineNumber}: satuan '{$unitName}' tidak ditemukan.";
                continue;
            }

            if ($barcode !== null) {
                if (isset($barcodeMap[$barcode])) {
                    $errors[] = "Baris {$lineNumber}: barcode '{$barcode}' duplikat di file import (sudah muncul pada baris {$barcodeMap[$barcode]}).";
                    continue;
                }

                if (Product::where('barcode', $barcode)->exists()) {
                    $errors[] = "Baris {$lineNumber}: barcode '{$barcode}' sudah ada di sistem.";
                    continue;
                }

                $barcodeMap[$barcode] = $lineNumber;
            }

            $validRows[] = [
                'code' => $this->generateUniqueCode($barcode, $name),
                'barcode' => $barcode,
                'name' => $name,
                'generic_name' => $genericName,
                'category_id' => $category->id,
                'base_unit_id' => $unit->id,
                'purchase_price' => 0,
                'selling_price' => 0,
                'min_stock' => $minStock,
                'max_stock' => $maxStock,
                'rack_location' => $rackLocation,
                'description' => $description,
                'requires_prescription' => false,
                'is_active' => true,
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
            Product::create($row);
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

    protected function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function generateUniqueCode(?string $barcode, string $name): string
    {
        $base = null;

        if ($barcode !== null) {
            $base = preg_replace('/[^A-Za-z0-9]+/', '', strtoupper($barcode));
        }

        if ($base === null || $base === '') {
            $base = Str::of($name)
                ->slug('-')
                ->upper()
                ->__toString();
        }

        $base = substr($base, 0, 32);
        $code = $base;
        $counter = 1;

        while (Product::where('code', $code)->exists()) {
            $suffix = (string) $counter;
            $code = substr($base, 0, 32 - strlen($suffix)).$suffix;
            $counter++;
        }

        return $code;
    }
}
