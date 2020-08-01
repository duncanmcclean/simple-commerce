<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDFFacade;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ReceiptShowRequest;
use Statamic\Facades\Entry;

class ReceiptController extends BaseActionController
{
    public function show(ReceiptShowRequest $request, $orderId)
    {
        $order = Entry::find($orderId);
        $data = $order->toAugmentedArray();

        return PDFFacade::loadView('simple-commerce::receipt', $data)
            ->download('receipt.pdf');
    }
}
