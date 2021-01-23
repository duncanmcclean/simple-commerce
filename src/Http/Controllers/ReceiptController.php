<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ReceiptShowRequest;

class ReceiptController extends BaseActionController
{
    public function show(ReceiptShowRequest $request, $orderId)
    {
        $cart = Order::find($orderId);

        return PDF::loadView('simple-commerce::receipt', array_merge($cart->entry()->toAugmentedArray(), [
            'orderId'          => $orderId,
            'shipping_address' => $cart->shippingAddress() !== null ? $cart->shippingAddress()->toArray() : [],
            'billing_address'  => $cart->billingAddress() !== null ? $cart->billingAddress()->toArray() : [],
        ]))->download('receipt.pdf');
    }
}
