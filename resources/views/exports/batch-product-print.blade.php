<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.3;
            padding: 20px;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }
        .store-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 4px;
        }
        .period,
        .print-date {
            font-size: 9px;
            margin-top: 2px;
        }
        .separator {
            margin: 8px 0;
            border-bottom: 1px dashed #000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        th,
        td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9px;
            text-align: left;
            vertical-align: top;
        }
        th {
            font-weight: bold;
            text-transform: uppercase;
            background: #f5f5f5;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 10px;
            font-size: 8px;
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="store-name">{{ $storeName }}</div>
        <div class="report-title">{{ $title }}</div>
        <div class="period">Periode: {{ $period }}</div>
        <div class="print-date">Cetak: {{ $printDate }}</div>
    </div>

    <div class="separator"></div>

    <table>
        <thead>
            <tr>
                @foreach($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}" class="text-center">Data tidak ditemukan</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Halaman ini dicetak untuk keperluan stock opname batch produk.
    </div>
</body>
</html>
