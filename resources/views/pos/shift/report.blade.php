<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Shift - {{ $shift->opening_time->format('d M Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
            padding: 20px;
            max-width: 80mm;
            margin: 0 auto;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: 80mm auto;
                margin: 5mm;
            }
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .store-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 10px 0;
        }
        .section {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #000;
        }
        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 11px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }
        .row.total {
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #000;
        }
        .label {
            flex: 1;
        }
        .value {
            text-align: right;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            text-align: left;
            padding: 2px 0;
            font-size: 11px;
        }
        th {
            border-bottom: 1px solid #000;
        }
        td.right, th.right {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }
        .print-btn:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">Cetak</button>

    <div class="header">
        <div class="store-name">{{ $store?->name ?? 'APOTEK' }}</div>
        @if($store?->address)
            <div>{{ $store->address }}</div>
        @endif
        @if($store?->phone)
            <div>Telp: {{ $store->phone }}</div>
        @endif
        <div class="report-title">LAPORAN SHIFT</div>
    </div>

    <div class="section">
        <div class="section-title">Informasi Shift</div>
        <div class="row">
            <span class="label">Kasir</span>
            <span class="value">{{ $shift->user?->name }}</span>
        </div>
        <div class="row">
            <span class="label">Waktu Buka</span>
            <span class="value">{{ $shift->opening_time->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Waktu Tutup</span>
            <span class="value">{{ $shift->closing_time?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Durasi</span>
            <span class="value">{{ $shift->opening_time->diffForHumans($shift->closing_time ?? now(), true) }}</span>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Ringkasan Penjualan</div>
        <div class="row">
            <span class="label">Jumlah Transaksi</span>
            <span class="value">{{ $salesSummary->total_transactions ?? 0 }}</span>
        </div>
        <div class="row">
            <span class="label">Total Penjualan</span>
            <span class="value">Rp {{ number_format($salesSummary->total_sales ?? 0, 0, ',', '.') }}</span>
        </div>
        @if(($salesSummary->total_discount ?? 0) > 0)
        <div class="row">
            <span class="label">Total Diskon</span>
            <span class="value">Rp {{ number_format($salesSummary->total_discount, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Rincian Pembayaran</div>
        @forelse($paymentBreakdown as $payment)
            <div class="row">
                <span class="label">{{ $payment->name }} ({{ $payment->transaction_count }}x)</span>
                <span class="value">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</span>
            </div>
        @empty
            <div>Tidak ada transaksi</div>
        @endforelse
    </div>

    @php
        // Selalu hitung ulang untuk mendapatkan nilai yang benar
        $expectedCash = $shift->calculateExpectedCash();
        $cashSalesTotal = $shift->getCashSalesTotal();
    @endphp
    <div class="section">
        <div class="section-title">Perhitungan Kas</div>
        <div class="row">
            <span class="label">Modal Awal</span>
            <span class="value">Rp {{ number_format($shift->opening_cash, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="label">Penjualan Tunai</span>
            <span class="value">+ Rp {{ number_format($cashSalesTotal, 0, ',', '.') }}</span>
        </div>
        <div class="row total">
            <span class="label">Kas Diharapkan</span>
            <span class="value">Rp {{ number_format($expectedCash, 0, ',', '.') }}</span>
        </div>
        @if($shift->actual_cash !== null)
        <div class="row">
            <span class="label">Kas Aktual</span>
            <span class="value">Rp {{ number_format($shift->actual_cash, 0, ',', '.') }}</span>
        </div>
        <div class="row">
            <span class="label">Selisih</span>
            <span class="value">{{ $shift->difference >= 0 ? '+' : '' }}Rp {{ number_format($shift->difference, 0, ',', '.') }}</span>
        </div>
        @endif
    </div>

    @if($itemsSold->count() > 0)
    <div class="section">
        <div class="section-title">Produk Terjual</div>
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($itemsSold as $item)
                <tr>
                    <td>{{ Str::limit($item->name, 20) }}</td>
                    <td class="right">{{ $item->total_qty }}</td>
                    <td class="right">{{ number_format($item->total_sales, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($sales->count() > 0)
    <div class="section">
        <div class="section-title">Daftar Transaksi</div>
        <table>
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Waktu</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sales as $sale)
                <tr>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->created_at->format('H:i') }}</td>
                    <td class="right">{{ number_format($sale->total, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <div>Dicetak: {{ now()->format('d/m/Y H:i:s') }}</div>
        <div style="margin-top: 30px;">
            <div>_______________________</div>
            <div style="margin-top: 5px;">{{ $shift->user?->name }}</div>
            <div>Kasir</div>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                window.print();
            }
            if (e.key === 'Escape') {
                window.close();
            }
        });
    </script>
</body>
</html>
