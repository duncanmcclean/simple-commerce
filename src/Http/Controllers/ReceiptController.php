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
        // $data = $order->data()->toArray();
        // $data['id'] = $order->id();
        // $data['title'] = $order->title;

        // dd($data['items']->value());

        return PDFFacade::loadView('simple-commerce::receipt', $data)
            ->download('receipt.pdf');
    }
}