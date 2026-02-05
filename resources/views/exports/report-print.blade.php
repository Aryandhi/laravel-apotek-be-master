<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        /* Style untuk dot matrix / continuous paper */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.2;
            color: #000;
            padding: 20px 30px;
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
            margin-top: 5px;
        }
        .period {
            font-size: 10px;
            margin-top: 3px;
        }
        .print-date {
            font-size: 9px;
            margin-top: 2px;
        }
        .separator {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        .summary {
            margin: 10px 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
        }
        .summary-label {
            font-weight: normal;
        }
        .summary-value {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        th {
            padding: 3px 2px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
        }
        td {
            padding: 2px;
            font-size: 9px;
            border-bottom: 1px dotted #ccc;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .total-row {
            font-weight: bold;
            border-top: 1px dashed #000;
        }
        .footer {
            margin-top: 10px;
            padding-top: 5px;
            border-top: 1px dashed #000;
            font-size: 8px;
            text-align: center;
        }
        /* Compact mode for dot matrix */
        .compact th,
        .compact td {
            padding: 1px 2px;
            font-size: 8px;
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

    @if(!empty($summary))
    <div class="summary">
        @foreach($summary as $key => $value)
        <div class="summary-row">
            <span class="summary-label">{{ $key }}:</span>
            <span class="summary-value">{{ $value }}</span>
        </div>
        @endforeach
    </div>
    <div class="separator"></div>
    @endif

    <table class="compact">
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
                <td colspan="{{ count($headings) }}" class="text-center">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        *** Akhir Laporan ***
    </div>
</body>
</html>
