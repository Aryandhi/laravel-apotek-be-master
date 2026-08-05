<?php

namespace App\Http\Controllers\Reports;

use App\Filament\Pages\Reports\BaseReport;
use App\Filament\Pages\Reports\BatchProductPrint;
use App\Filament\Pages\Reports\ProfitLossReport;
use App\Filament\Pages\Reports\PurchaseReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Filament\Pages\Reports\StockMovementReport;
use App\Filament\Pages\Reports\StockReport;
use App\Filament\Pages\Reports\TopProductsReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportPreviewController extends Controller
{
    /**
     * @var array<string, class-string<BaseReport>>
     */
    private const REPORT_PAGE_MAP = [
        'sales' => SalesReport::class,
        'purchase' => PurchaseReport::class,
        'stock' => StockReport::class,
        'profit-loss' => ProfitLossReport::class,
        'top-products' => TopProductsReport::class,
        'stock-movement' => StockMovementReport::class,
        'batch-product-print' => BatchProductPrint::class,
    ];

    public function showPage(Request $request, string $report)
    {
        $reportPage = $this->resolveReportPage($report, $request);

        return response()->view('reports.preview', [
            'title' => $reportPage->getTitle(),
            'inlinePdfUrl' => $reportPage->getPreviewPdfUrl(),
            'downloadPdfUrl' => $reportPage->getPreviewPdfUrl(true),
        ]);
    }

    public function showPdf(Request $request, string $report)
    {
        $reportPage = $this->resolveReportPage($report, $request);

        $pdf = Pdf::loadView($reportPage->getPrintViewName(), $reportPage->getPreviewPayload());
        $pdf->setPaper($reportPage->getPrintPaperSize(), $reportPage->getPrintPaperOrientation());

        $isDownload = $request->boolean('download');
        $disposition = $isDownload ? 'attachment' : 'inline';

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $disposition.'; filename="'.$reportPage->getPrintPdfFilename().'"');
    }

    private function resolveReportPage(string $report, Request $request): BaseReport
    {
        $pageClass = self::REPORT_PAGE_MAP[$report] ?? null;

        if ($pageClass === null) {
            throw new NotFoundHttpException;
        }

        $reportPage = app($pageClass);
        $reportPage->mount();
        $reportPage->applyPreviewFilters($request->query());

        return $reportPage;
    }
}
