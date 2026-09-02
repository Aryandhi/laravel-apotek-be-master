<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PurchaseOrderPrintController extends Controller
{
    public function show(Request $request, PurchaseOrder $purchaseOrder)
    {
        abort_unless($request->user()?->can('purchase-orders.print'), 403);
        abort_unless($purchaseOrder->status->isPrintable(), 403, 'Surat Pesanan belum berstatus Order.');

        $purchaseOrder->load(['items.product', 'items.unit', 'supplier']);

        $view = $purchaseOrder->group->usesNarcoticTemplate()
            ? 'exports.purchase-order-narcotic'
            : 'exports.purchase-order-standard';

        $pdf = Pdf::loadView($view, [
            'order' => $purchaseOrder,
            'store' => Store::query()->first(),
        ]);

        $pdf->setPaper('a4', 'portrait');

        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="'.$purchaseOrder->po_number.'.pdf"');
    }
}
