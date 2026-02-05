<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=80mm, initial-scale=1.0">
    <title>Struk {{ $sale->invoice_number }}</title>
    <style>
        /* Reset & Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Thermal Paper 80mm = ~302px at 96dpi, but we use mm for print */
        @page {
            size: 80mm auto;
            margin: 0;
        }

        body {
            font-family: 'Courier New', 'Lucida Console', monospace;
            font-size: 12px;
            line-height: 1.3;
            width: 80mm;
            margin: 0 auto;
            padding: 2mm;
            background: #fff;
            color: #000;
        }

        /* For screen preview */
        @media screen {
            body {
                width: 302px;
                padding: 8px;
                border: 1px solid #ccc;
                margin: 20px auto;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
        }

        /* Print styles */
        @media print {
            body {
                width: 80mm;
                padding: 2mm;
            }

            .no-print {
                display: none !important;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 8px;
        }

        .store-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .store-info {
            font-size: 10px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .divider-double {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        .invoice-info {
            font-size: 11px;
        }

        .items {
            margin: 6px 0;
        }

        .item {
            margin-bottom: 4px;
        }

        .item-name {
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding-left: 8px;
        }

        .totals {
            margin: 6px 0;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
        }

        .total-row.grand {
            font-weight: bold;
            font-size: 13px;
            border-top: 1px solid #000;
            padding-top: 4px;
            margin-top: 4px;
        }

        .payments {
            margin: 6px 0;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 8px;
        }

        .footer-message {
            margin-top: 8px;
            font-size: 11px;
        }

        /* Print button for screen */
        .print-actions {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 100;
        }

        .btn {
            padding: 12px 24px;
            font-size: 14px;
            font-weight: bold;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-print {
            background: #059669;
            color: white;
        }

        .btn-print:hover {
            background: #047857;
        }

        .btn-close {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-close:hover {
            background: #d1d5db;
        }

        .prescription-badge {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            font-size: 9px;
            padding: 2px 6px;
            display: inline-block;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <!-- Receipt Content -->
    <div class="receipt">
        <!-- Store Header -->
        <div class="header">
            <div class="store-name">{{ $store?->name ?? 'APOTEK' }}</div>
            @if($store?->address)
                <div class="store-info">{{ $store->address }}</div>
            @endif
            @if($store?->phone)
                <div class="store-info">Telp: {{ $store->phone }}</div>
            @endif
            @if($store?->sia_number)
                <div class="store-info">SIA: {{ $store->sia_number }}</div>
            @endif
        </div>

        <div class="divider-double"></div>

        <!-- Invoice Info -->
        <div class="invoice-info">
            <div>No   : {{ $sale->invoice_number }}</div>
            <div>Tgl  : {{ $sale->created_at->format('d/m/Y H:i') }}</div>
            <div>Kasir: {{ $sale->user?->name ?? '-' }}</div>
            @if($sale->customer)
                <div>Plgn : {{ $sale->customer->name }}</div>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <div class="items">
            @foreach($sale->items as $item)
                <div class="item">
                    <div class="item-name">{{ Str::limit($item->product?->name ?? 'Produk', 32) }}</div>
                    <div class="item-detail">
                        <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($sale->discount > 0)
                <div class="total-row">
                    <span>Diskon</span>
                    <span>- Rp {{ number_format($sale->discount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="total-row grand">
                <span>TOTAL</span>
                <span>Rp {{ number_format($sale->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Payments -->
        <div class="payments">
            @foreach($sale->payments as $payment)
                <div class="total-row">
                    <span>{{ $payment->paymentMethod?->name ?? 'Tunai' }}</span>
                    <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
            @endforeach
            @if($sale->change_amount > 0)
                <div class="total-row">
                    <span>Kembali</span>
                    <span>Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <div class="divider-double"></div>

        <!-- Footer -->
        <div class="footer">
            @if($store?->pharmacist_name)
                <div>Apoteker: {{ $store->pharmacist_name }}</div>
                @if($store->pharmacist_sipa)
                    <div>SIPA: {{ $store->pharmacist_sipa }}</div>
                @endif
            @endif

            @if($sale->is_prescription)
                <div class="prescription-badge">OBAT DENGAN RESEP DOKTER</div>
            @endif

            <div class="footer-message">
                {{ $store?->receipt_footer ?? 'Terima Kasih' }}<br>
                Semoga Lekas Sembuh
            </div>

            <div style="margin-top: 8px; font-size: 9px; color: #666;">
                {{ $sale->created_at->format('d/m/Y H:i:s') }}
            </div>
        </div>
    </div>

    <!-- Print Actions (Screen only) -->
    <div class="print-actions no-print">
        <button type="button" class="btn btn-print" onclick="window.print()">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak Struk
        </button>
        <button type="button" class="btn btn-close" onclick="window.close()">
            Tutup
        </button>
    </div>

    <script>
        // Auto print on load (optional, uncomment if needed)
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 500);
        // };

        // Handle after print
        window.onafterprint = function() {
            // Optional: close window after print
            // window.close();
        };

        // Keyboard shortcut: Ctrl+P or Enter to print
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || (e.ctrlKey && e.key === 'p')) {
                e.preventDefault();
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
