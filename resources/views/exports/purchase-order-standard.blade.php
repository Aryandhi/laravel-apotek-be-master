<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $order->po_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000; padding: 30px 40px; }
        .kop { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .kop .name { font-size: 16px; font-weight: bold; text-transform: uppercase; }
        .kop .address { font-size: 11px; margin-top: 2px; }
        .title { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin: 14px 0 4px; text-transform: uppercase; }
        .po-number { text-align: center; font-size: 12px; margin-bottom: 14px; }
        .meta-table { width: 100%; margin-bottom: 12px; }
        .meta-table td { padding: 2px 4px; vertical-align: top; font-size: 12px; }
        table.items { width: 100%; border-collapse: collapse; margin: 12px 0 20px; }
        table.items th, table.items td { border: 1px solid #000; padding: 5px 8px; font-size: 11px; }
        table.items th { background: #f0f0f0; text-align: center; }
        table.items td.center { text-align: center; }
        .signature-block { width: 100%; margin-top: 30px; }
        .signature-col { display: inline-block; width: 45%; vertical-align: top; text-align: center; }
        .signature-space { height: 70px; }
    </style>
</head>
<body>
    <div class="kop">
        <div class="name">{{ $store?->name ?? config('app.name') }}</div>
        <div class="address">{{ $store?->address }}</div>
        <div class="address">
            @if($store?->phone) Telp: {{ $store->phone }} @endif
            @if($store?->sia_number) &nbsp;|&nbsp; No. SIA: {{ $store->sia_number }} @endif
        </div>
    </div>

    <div class="title">{{ $order->group->documentTitle() }}</div>
    <div class="po-number">Nomor: {{ $order->po_number }} &nbsp;|&nbsp; Tanggal: {{ $order->order_date->format('d F Y') }}</div>

    <table class="meta-table">
        <tr>
            <td style="width: 100px;">Kepada Yth.</td>
            <td>:</td>
            <td>{{ $order->supplier?->name }}</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>{{ $order->supplier?->address ?? '-' }}</td>
        </tr>
    </table>

    <p>Dengan ini kami mengajukan pesanan barang sebagai berikut:</p>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Nama Obat</th>
                <th style="width: 90px;">Satuan Kemasan</th>
                <th style="width: 90px;">Jumlah Pesanan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $item->product?->name }}@if($item->product?->generic_name) ({{ $item->product->generic_name }}) @endif</td>
                    <td class="center">{{ $item->unit?->name }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-block">
        <div class="signature-col">
            &nbsp;
        </div>
        <div class="signature-col">
            <div>{{ $store?->address ? explode(',', $store->address)[0] : '' }}, {{ $order->order_date->format('d F Y') }}</div>
            <div>Apoteker Penanggung Jawab,</div>
            <div class="signature-space"></div>
            <div><strong>{{ $store?->pharmacist_name ?? '-' }}</strong></div>
            <div>SIPA: {{ $store?->pharmacist_sipa ?? '-' }}</div>
        </div>
    </div>
</body>
</html>
