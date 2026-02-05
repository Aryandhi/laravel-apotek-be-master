<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Store;
use App\Services\ReceiptPdfService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ReceiptController extends Controller
{
    public function show(Sale $sale): View
    {
        $sale->load([
            'customer',
            'items.product',
            'items.productBatch',
            'items.unit',
            'payments.paymentMethod',
            'user',
        ]);

        $store = Store::first();

        return view('pos.receipts.show', compact('sale', 'store'));
    }

    public function print(Sale $sale): Response
    {
        $sale->load([
            'customer',
            'items.product',
            'items.productBatch',
            'items.unit',
            'payments.paymentMethod',
            'user',
        ]);

        $pdfService = new ReceiptPdfService;
        $filename = ($sale->invoice_number ?: 'struk').'.pdf';

        // Generate PDF to string
        $pdfContent = $pdfService->output($sale, 'S');

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$filename.'"');
    }

    public function escpos(Sale $sale): Response
    {
        $sale->load([
            'customer',
            'items.product',
            'items.productBatch',
            'items.unit',
            'payments.paymentMethod',
            'user',
        ]);

        $store = Store::first();

        $commands = $this->generateEscPosCommands($sale, $store);

        return response($commands)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="receipt_'.$sale->invoice_number.'.bin"');
    }

    /**
     * Get ESC/POS commands as JSON array for QZ Tray.
     */
    public function escposJson(Sale $sale): \Illuminate\Http\JsonResponse
    {
        $sale->load([
            'customer',
            'items.product',
            'items.productBatch',
            'items.unit',
            'payments.paymentMethod',
            'user',
        ]);

        $store = Store::first();

        $commands = $this->generateEscPosArray($sale, $store);

        return response()->json([
            'success' => true,
            'data' => [
                'commands' => $commands,
                'sale' => [
                    'id' => $sale->id,
                    'invoice_number' => $sale->invoice_number,
                ],
            ],
        ]);
    }

    /**
     * Generate ESC/POS commands as array for QZ Tray.
     */
    private function generateEscPosArray(Sale $sale, ?Store $store): array
    {
        $commands = [];

        // Initialize printer
        $commands[] = '\x1B\x40'; // Reset

        // Center alignment
        $commands[] = '\x1B\x61\x01';

        // Double height for store name
        $commands[] = '\x1B\x21\x10';
        $commands[] = ($store?->name ?? 'APOTEK')."\n";

        // Normal size
        $commands[] = '\x1B\x21\x00';

        if ($store?->address) {
            $commands[] = $store->address."\n";
        }
        if ($store?->phone) {
            $commands[] = 'Telp: '.$store->phone."\n";
        }
        if ($store?->sia_number) {
            $commands[] = 'SIA: '.$store->sia_number."\n";
        }

        // Line separator
        $commands[] = str_repeat('=', 42)."\n";

        // Left alignment
        $commands[] = '\x1B\x61\x00';

        // Invoice info
        $commands[] = 'No: '.$sale->invoice_number."\n";
        $commands[] = 'Tgl: '.$sale->created_at->format('d/m/Y H:i')."\n";
        $commands[] = 'Kasir: '.($sale->user?->name ?? '-')."\n";
        if ($sale->customer) {
            $commands[] = 'Pelanggan: '.$sale->customer->name."\n";
        }

        $commands[] = str_repeat('-', 42)."\n";

        // Items
        foreach ($sale->items as $item) {
            $name = mb_substr($item->product?->name ?? 'Produk', 0, 24);
            $commands[] = $name."\n";

            $qty = $item->quantity.' x '.$this->formatNumber($item->price);
            $subtotal = $this->formatNumber($item->subtotal);
            $commands[] = '  '.$this->padBetween($qty, $subtotal, 40)."\n";
        }

        $commands[] = str_repeat('-', 42)."\n";

        // Totals
        $commands[] = $this->padBetween('Subtotal', 'Rp '.$this->formatNumber($sale->subtotal), 42)."\n";

        if ($sale->discount > 0) {
            $commands[] = $this->padBetween('Diskon', '- Rp '.$this->formatNumber($sale->discount), 42)."\n";
        }

        // Bold for total
        $commands[] = '\x1B\x45\x01';
        $commands[] = $this->padBetween('TOTAL', 'Rp '.$this->formatNumber($sale->total), 42)."\n";
        $commands[] = '\x1B\x45\x00';

        $commands[] = str_repeat('-', 42)."\n";

        // Payments
        foreach ($sale->payments as $payment) {
            $method = $payment->paymentMethod?->name ?? 'Tunai';
            $commands[] = $this->padBetween($method, 'Rp '.$this->formatNumber($payment->amount), 42)."\n";
        }

        if ($sale->change_amount > 0) {
            $commands[] = $this->padBetween('Kembali', 'Rp '.$this->formatNumber($sale->change_amount), 42)."\n";
        }

        $commands[] = str_repeat('=', 42)."\n";

        // Center for footer
        $commands[] = '\x1B\x61\x01';

        if ($store?->pharmacist_name) {
            $commands[] = 'Apoteker: '.$store->pharmacist_name."\n";
            if ($store->pharmacist_sipa) {
                $commands[] = 'SIPA: '.$store->pharmacist_sipa."\n";
            }
        }

        $commands[] = "\n";
        $commands[] = ($store?->receipt_footer ?? 'Terima Kasih')."\n";
        $commands[] = "Semoga Lekas Sembuh\n";

        // Feed and cut
        $commands[] = "\n\n\n";
        $commands[] = '\x1D\x56\x00'; // Full cut

        return $commands;
    }

    private function generateEscPosCommands(Sale $sale, ?Store $store): string
    {
        $esc = "\x1B";
        $gs = "\x1D";

        $commands = '';

        // Initialize printer
        $commands .= $esc.'@'; // Reset

        // Center alignment
        $commands .= $esc."a\x01";

        // Double height for store name
        $commands .= $esc."!\x10";
        $commands .= ($store?->name ?? 'APOTEK')."\n";

        // Normal size
        $commands .= $esc."!\x00";

        if ($store?->address) {
            $commands .= $store->address."\n";
        }
        if ($store?->phone) {
            $commands .= 'Telp: '.$store->phone."\n";
        }
        if ($store?->sia_number) {
            $commands .= 'SIA: '.$store->sia_number."\n";
        }

        // Line separator
        $commands .= str_repeat('=', 42)."\n";

        // Left alignment
        $commands .= $esc."a\x00";

        // Invoice info
        $commands .= 'No: '.$sale->invoice_number."\n";
        $commands .= 'Tgl: '.$sale->created_at->format('d/m/Y H:i')."\n";
        $commands .= 'Kasir: '.($sale->user?->name ?? '-')."\n";
        if ($sale->customer) {
            $commands .= 'Pelanggan: '.$sale->customer->name."\n";
        }

        $commands .= str_repeat('-', 42)."\n";

        // Items
        foreach ($sale->items as $item) {
            $name = mb_substr($item->product?->name ?? 'Produk', 0, 24);
            $commands .= $name."\n";

            $qty = $item->quantity.' x '.$this->formatNumber($item->price);
            $subtotal = $this->formatNumber($item->subtotal);
            $commands .= '  '.$this->padBetween($qty, $subtotal, 40)."\n";
        }

        $commands .= str_repeat('-', 42)."\n";

        // Totals
        $commands .= $this->padBetween('Subtotal', 'Rp '.$this->formatNumber($sale->subtotal), 42)."\n";

        if ($sale->discount > 0) {
            $commands .= $this->padBetween('Diskon', '- Rp '.$this->formatNumber($sale->discount), 42)."\n";
        }

        // Bold for total
        $commands .= $esc."E\x01";
        $commands .= $this->padBetween('TOTAL', 'Rp '.$this->formatNumber($sale->total), 42)."\n";
        $commands .= $esc."E\x00";

        $commands .= str_repeat('-', 42)."\n";

        // Payments
        foreach ($sale->payments as $payment) {
            $method = $payment->paymentMethod?->name ?? 'Tunai';
            $commands .= $this->padBetween($method, 'Rp '.$this->formatNumber($payment->amount), 42)."\n";
        }

        if ($sale->change_amount > 0) {
            $commands .= $this->padBetween('Kembali', 'Rp '.$this->formatNumber($sale->change_amount), 42)."\n";
        }

        $commands .= str_repeat('=', 42)."\n";

        // Center for footer
        $commands .= $esc."a\x01";

        if ($store?->pharmacist_name) {
            $commands .= 'Apoteker: '.$store->pharmacist_name."\n";
            if ($store->pharmacist_sipa) {
                $commands .= 'SIPA: '.$store->pharmacist_sipa."\n";
            }
        }

        $commands .= "\n";
        $commands .= ($store?->receipt_footer ?? 'Terima Kasih')."\n";
        $commands .= "Semoga Lekas Sembuh\n";

        // Feed and cut
        $commands .= "\n\n\n";
        $commands .= $gs."V\x00"; // Full cut

        return $commands;
    }

    private function formatNumber(float $number): string
    {
        return number_format($number, 0, ',', '.');
    }

    private function padBetween(string $left, string $right, int $width): string
    {
        $leftLen = mb_strlen($left);
        $rightLen = mb_strlen($right);
        $spaces = $width - $leftLen - $rightLen;

        if ($spaces < 1) {
            $spaces = 1;
        }

        return $left.str_repeat(' ', $spaces).$right;
    }
}
