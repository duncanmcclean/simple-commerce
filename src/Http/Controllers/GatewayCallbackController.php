<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayCallbackController extends BaseActionController
{
    use CartDriver;

    public function index(Request $request, $gateway)
    {
        $gateway = collect(SimpleCommerce::gateways())
            ->where('handle', $gateway)
            ->first();

        if (!$gateway) {
            throw new GatewayDoesNotExist(__('simple-commerce::messages.gateway_does_not_exist', [
                'gateway' => $gateway['name'],
            ]));
        }

        if ($request->has('_order_id') && $request->has('_error_redirect')) {
            $order = Order::find($request->get('_order_id'));

            if ($order->get('is_paid') === false) {
                return $this->withErrors($request, "Order [{$order->id()}] has not been marked as paid yet.");
            }
        }

        $this->forgetCart();

        return $this->withSuccess($request, [
            'success' => __('simple-commerce.messages.checkout_complete'),
        ]);
    }
}
