<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Exceptions\GatewayCallbackMethodDoesNotExist;
use DuncanMcClean\SimpleCommerce\Exceptions\GatewayDoesNotExist;
use DuncanMcClean\SimpleCommerce\Facades\Gateway;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;

class GatewayCallbackController extends BaseActionController
{
    use CartDriver;

    public function index(Request $request, $gateway)
    {
        if ($request->has('_order_id')) {
            $order = Order::find($request->get('_order_id'));
        } else {
            $order = $this->getCart();
        }

        $gatewayName = $gateway;

        $gateway = SimpleCommerce::gateways()
            ->where('handle', $gateway)
            ->first();

        if (! $gateway) {
            throw new GatewayDoesNotExist("Gateway [{$gatewayName}] does not exist.");
        }

        try {
            $callbackSuccess = Gateway::use($gateway['handle'])->callback($request);
        } catch (GatewayCallbackMethodDoesNotExist $e) {
            $callbackSuccess = $order->paymentStatus() === PaymentStatus::Paid;
        }

        if (! $callbackSuccess) {
            return $this->withErrors($request, "Order [{$order->get('title')}] has not been marked as paid yet.");
        }

        $order->status(OrderStatus::Placed)->save();

        $this->forgetCart();

        return $this->withSuccess($request, [
            'success' => __('Checkout Complete!'),
            'cart' => $order->toAugmentedArray(),
            'is_checkout_request' => true,
        ]);
    }
}
