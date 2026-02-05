<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Store;
use FPDF;

class PDF_Receipt extends FPDF
{
    protected $widths = [];

    protected $aligns = [];

    public function SetWidths($w): void
    {
        $this->widths = $w;
    }

    public function SetAligns($a): void
    {
        $this->aligns = $a;
    }

    public function Row($data, $fontSize = 8, $fontStyle = ''): void
    {
        $nb = 0;
        for ($i = 0; $i < count($data); $i++) {
            $nb = max($nb, $this->NbLines($this->widths[$i], $data[$i]));
        }
        $h = 4 * $nb;
        $this->CheckPageBreak($h);

        for ($i = 0; $i < count($data); $i++) {
            $w = $this->widths[$i];
            $a = $this->aligns[$i] ?? 'L';
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetFont('Courier', $fontStyle, $fontSize);
            $this->MultiCell($w, 4, $this->enc($data[$i]), 0, $a);
            $this->SetXY($x + $w, $y);
        }
        $this->Ln($h);
    }

    public function CheckPageBreak($h): void
    {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    public function NbLines($w, $txt): int
    {
        $cw = 0.35;
        $maxChars = max(1, floor($w / $cw));
        $lines = 0;
        foreach (preg_split("/\r\n|\n|\r/", (string) $txt) as $line) {
            $len = mb_strlen($line, 'UTF-8');
            $lines += max(1, ceil($len / $maxChars));
        }

        return $lines;
    }

    public function enc($s): string
    {
        if ($s === null) {
            return '';
        }

        return iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', (string) $s);
    }
}

class ReceiptPdfService
{
    protected $pdf;

    protected $paperW = 80; // 80mm thermal printer

    protected $paperH = 200; // Dynamic height

    protected $marginL = 3;

    protected $marginR = 3;

    protected $marginT = 3;

    protected $marginB = 3;

    protected $contentW;

    public function __construct()
    {
        $this->contentW = $this->paperW - $this->marginL - $this->marginR;
    }

    protected function rupiah($n): string
    {
        return number_format((float) $n, 0, ',', '.');
    }

    protected function fmtQty($q): string
    {
        $q = (float) $q;
        $t = rtrim(rtrim(number_format($q, 2, ',', '.'), '0'), ',');

        return $t === '' ? '0' : $t;
    }

    public function generate(Sale $sale): PDF_Receipt
    {
        $this->pdf = new PDF_Receipt('P', 'mm', [$this->paperW, $this->paperH]);
        $this->pdf->SetMargins($this->marginL, $this->marginT, $this->marginR);
        $this->pdf->SetAutoPageBreak(true, $this->marginB);
        $this->pdf->AddPage();

        // Get store data
        $store = Store::first();

        // ======= HEADER =======
        $storeName = $store?->name ?? 'APOTEK';
        $storeAddress = $store?->address ?? '';
        $storePhone = $store?->phone ?? '';
        $siaNumber = $store?->sia_number ?? '';

        $this->pdf->SetFont('Courier', 'B', 10);
        $this->pdf->Cell(0, 4, $this->pdf->enc($storeName), 0, 1, 'C');

        if ($storeAddress) {
            $this->pdf->SetFont('Courier', '', 7);
            $this->pdf->MultiCell(0, 3, $this->pdf->enc($storeAddress), 0, 'C');
        }

        if ($storePhone) {
            $this->pdf->SetFont('Courier', '', 7);
            $this->pdf->Cell(0, 3, $this->pdf->enc('Telp: '.$storePhone), 0, 1, 'C');
        }

        if ($siaNumber) {
            $this->pdf->SetFont('Courier', '', 7);
            $this->pdf->Cell(0, 3, $this->pdf->enc('SIA: '.$siaNumber), 0, 1, 'C');
        }

        $this->pdf->Ln(1);

        // Separator line
        $this->pdf->SetFont('Courier', '', 7);
        $this->pdf->Cell(0, 2, str_repeat('-', 50), 0, 1, 'C');
        $this->pdf->Ln(1);

        // ======= INFO TRANSAKSI =======
        $this->pdf->SetFont('Courier', '', 7);
        $this->pdf->Cell(0, 3, $this->pdf->enc('No: '.$sale->invoice_number), 0, 1, 'L');
        $this->pdf->Cell(0, 3, $this->pdf->enc('Tgl: '.$sale->created_at->format('d/m/Y H:i')), 0, 1, 'L');
        $this->pdf->Cell(0, 3, $this->pdf->enc('Kasir: '.($sale->user->name ?? '-')), 0, 1, 'L');

        if ($sale->customer) {
            $this->pdf->Cell(0, 3, $this->pdf->enc('Pelanggan: '.$sale->customer->name), 0, 1, 'L');
        }

        $this->pdf->Ln(1);
        $this->pdf->Cell(0, 2, str_repeat('-', 50), 0, 1, 'C');
        $this->pdf->Ln(1);

        // ======= ITEM TABLE =======
        $colQty = 8;
        $colPrice = 18;
        $colTotal = 18;
        $colName = $this->contentW - ($colQty + $colPrice + $colTotal);

        $this->pdf->SetWidths([$colName, $colQty, $colPrice, $colTotal]);
        $this->pdf->SetAligns(['L', 'C', 'R', 'R']);

        // Header
        $this->pdf->SetFont('Courier', 'B', 7);
        $this->pdf->Row(['Item', 'Qty', 'Harga', 'Total'], 7, 'B');

        // Items
        $this->pdf->SetFont('Courier', '', 7);
        foreach ($sale->items as $item) {
            $productName = $item->product?->name ?? 'Product';
            // Truncate long names
            if (mb_strlen($productName) > 20) {
                $productName = mb_substr($productName, 0, 18).'..';
            }

            $this->pdf->Row([
                $productName,
                $this->fmtQty($item->quantity),
                $this->rupiah($item->price),
                $this->rupiah($item->subtotal),
            ], 7);
        }

        $this->pdf->Ln(1);
        $this->pdf->Cell(0, 2, str_repeat('-', 50), 0, 1, 'C');
        $this->pdf->Ln(1);

        // ======= TOTALS =======
        $labelW = $this->contentW - 25;
        $valueW = 25;

        $this->pdf->SetFont('Courier', '', 8);

        // Subtotal
        $this->pdf->Cell($labelW, 4, $this->pdf->enc('Subtotal'), 0, 0, 'R');
        $this->pdf->Cell($valueW, 4, $this->pdf->enc($this->rupiah($sale->subtotal)), 0, 1, 'R');

        // Discount
        if ((float) $sale->discount > 0) {
            $this->pdf->Cell($labelW, 4, $this->pdf->enc('Diskon'), 0, 0, 'R');
            $this->pdf->Cell($valueW, 4, $this->pdf->enc('-'.$this->rupiah($sale->discount)), 0, 1, 'R');
        }

        // Tax
        if ((float) $sale->tax > 0) {
            $this->pdf->Cell($labelW, 4, $this->pdf->enc('Pajak'), 0, 0, 'R');
            $this->pdf->Cell($valueW, 4, $this->pdf->enc($this->rupiah($sale->tax)), 0, 1, 'R');
        }

        // Total
        $this->pdf->SetFont('Courier', 'B', 9);
        $this->pdf->Cell($labelW, 5, $this->pdf->enc('TOTAL'), 0, 0, 'R');
        $this->pdf->Cell($valueW, 5, $this->pdf->enc($this->rupiah($sale->total)), 0, 1, 'R');

        // Paid
        $this->pdf->SetFont('Courier', '', 8);
        $this->pdf->Cell($labelW, 4, $this->pdf->enc('Bayar'), 0, 0, 'R');
        $this->pdf->Cell($valueW, 4, $this->pdf->enc($this->rupiah($sale->paid_amount)), 0, 1, 'R');

        // Change
        $this->pdf->Cell($labelW, 4, $this->pdf->enc('Kembali'), 0, 0, 'R');
        $this->pdf->Cell($valueW, 4, $this->pdf->enc($this->rupiah($sale->change_amount)), 0, 1, 'R');

        // Payment Method
        $paymentMethod = $sale->payments->first()?->paymentMethod?->name ?? 'Tunai';
        $this->pdf->Ln(1);
        $this->pdf->SetFont('Courier', '', 7);
        $this->pdf->Cell(0, 3, $this->pdf->enc('Pembayaran: '.$paymentMethod), 0, 1, 'L');

        $this->pdf->Ln(1);
        $this->pdf->Cell(0, 2, str_repeat('-', 50), 0, 1, 'C');
        $this->pdf->Ln(2);

        // ======= FOOTER =======
        // Pharmacist info
        $pharmacistName = $store?->pharmacist_name ?? '';
        $pharmacistSipa = $store?->pharmacist_sipa ?? '';

        if ($pharmacistName) {
            $this->pdf->SetFont('Courier', '', 7);
            $this->pdf->Cell(0, 3, $this->pdf->enc('Apoteker: '.$pharmacistName), 0, 1, 'C');
            if ($pharmacistSipa) {
                $this->pdf->Cell(0, 3, $this->pdf->enc('SIPA: '.$pharmacistSipa), 0, 1, 'C');
            }
            $this->pdf->Ln(1);
        }

        // Receipt footer message
        $receiptFooter = $store?->receipt_footer ?? 'Terima kasih atas kunjungan Anda';
        $this->pdf->SetFont('Courier', '', 7);
        $this->pdf->MultiCell(0, 3, $this->pdf->enc($receiptFooter), 0, 'C');
        $this->pdf->Cell(0, 3, $this->pdf->enc('Semoga lekas sembuh'), 0, 1, 'C');

        $this->pdf->Ln(2);
        $this->pdf->SetFont('Courier', '', 6);
        $this->pdf->Cell(0, 3, $this->pdf->enc('Barang yang sudah dibeli'), 0, 1, 'C');
        $this->pdf->Cell(0, 3, $this->pdf->enc('tidak dapat dikembalikan'), 0, 1, 'C');

        return $this->pdf;
    }

    public function output(Sale $sale, string $destination = 'I'): string
    {
        $pdf = $this->generate($sale);
        $filename = ($sale->invoice_number ?: 'struk').'.pdf';

        return $pdf->Output($destination, $filename);
    }
}
