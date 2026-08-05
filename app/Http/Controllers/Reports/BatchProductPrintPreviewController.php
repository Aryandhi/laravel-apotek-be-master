<?php

namespace App\Http\Controllers\Reports;

use App\Filament\Pages\Reports\BatchProductPrint;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class BatchProductPrintPreviewController extends Controller
{
    public function show(Request $request)
    {
        $page = new BatchProductPrint();
        $page->productId = $request->query('product_id', '');
        $page->rackLocation = $request->query('rack_location', '');

        $pdf = Pdf::loadView('exports.batch-product-print', [
            'title' => $page->getReportTitle(),
            'period' => $page->startDate ? $page->startDate.' - '.$page->endDate : 'Semua data',
            'headings' => $page->getExportHeadings(),
            'data' => $page->getExportData(),
            'summary' => $page->getSummaryData(),
            'storeName' => config('app.name'),
            'printDate' => now()->format('d/m/Y H:i'),
        ]);

        $pdf->setPaper([0, 0, 612, 936], 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="batch-product-preview.pdf"');
    }
}
