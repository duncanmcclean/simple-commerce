<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDFFacade;
use Illuminate\Http\Request;
use Statamic\Facades\Entry;

class ReceiptController extends BaseActionController
{
    public function show(Request $request, $orderId)
    {
        if (! $request->hasValidSignature()) {
            abort(401);
        }

        $order = Entry::find($orderId);
        $data = $order->toAugmentedArray();

        return PDFFacade::loadView('simple-commerce::receipt', $data)
            ->download('receipt.pdf');
    }
}