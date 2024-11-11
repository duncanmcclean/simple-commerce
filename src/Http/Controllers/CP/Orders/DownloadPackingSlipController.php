<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\CP\Orders;

use Dompdf\Dompdf;
use Dompdf\Options;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Http\Request;
use Statamic\Http\Controllers\CP\CpController;
use Facades\Statamic\Console\Processes\Composer;

class DownloadPackingSlipController extends CpController
{
    public function __invoke(Request $request, $order)
    {
        if (! Composer::isInstalled('dompdf/dompdf')) {
            throw new \Exception("Please require `dompdf/dompdf` to print packing slips.");
        }

        $order = Order::find($order);

        $this->authorize('edit', $order);

        $dompdfOptions = new Options;
        $dompdfOptions->setChroot(base_path());

        $pdf = (new Dompdf($dompdfOptions))->setPaper('letter');

        $pdf->loadHtml(view('simple-commerce::packing-slip', [
            'config' => config()->all(),
            'order' => $order,
        ])->render());

        // todo: this doesn't seem to be returning any pages...
        return response($pdf->render())->header('Content-Type', 'application/pdf');
    }
}