<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ReceiptShowRequest;

class ReceiptController extends BaseActionController
{
    public function show(ReceiptShowRequest $request, $orderId)
    {
        $cart = Cart::find($orderId);

        return PDF::loadView('simple-commerce::receipt', array_merge($cart->entry()->toAugmentedArray(), [
            'orderId'  => $orderId,
            'shipping_address' => $cart->shippingAddress()->toArray(),
            'billing_address'  => $cart->billingAddress()->toArray(),
        ]))->download('receipt.pdf');
    }
}
