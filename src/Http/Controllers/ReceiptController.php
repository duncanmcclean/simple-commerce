<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use Barryvdh\DomPDF\Facade as PDF;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ReceiptShowRequest;

class ReceiptController extends BaseActionController
{
    public function show(ReceiptShowRequest $request, $orderId)
    {
        $order = Order::find($orderId);

        $data = array_merge($order->toAugmentedArray(), [
            // 'orderId'          => $orderId,
            // 'shipping_address' => $order->shippingAddress(),
            // 'billing_address'  => $order->billingAddress(),
            'config' => [
                'app' => config('app'),
            ],
        ]);

        return PDF::loadView('simple-commerce::receipt', $data)
            ->download('receipt.pdf');
    }
}
