<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\HandlesCustomerInformation;
use DuncanMcClean\SimpleCommerce\Http\Requests\AcceptsFormRequests;
use DuncanMcClean\SimpleCommerce\Orders\Blueprint;
use Illuminate\Http\Request;

class CheckoutController extends BaseActionController
{
    use AcceptsFormRequests, HandlesCustomerInformation;

    public function __invoke(Request $request)
    {
        $values = $request->all();

        // TODO: This is dumb. Find a better way.
        if (isset($values['use_shipping_address_for_billing'])) {
            $values['use_shipping_address_for_billing'] = $values['use_shipping_address_for_billing'] === 'on';
        }

        $validated = Blueprint::getBlueprint()->fields()->addValues($values)->validate();

        $cart = Cart::current();
        $cart = $this->handleCustomerInformation($request, $cart);
        $cart->merge($validated);
        $cart->save();

        $order = Order::makeFromCart($cart);
        $order->save();

        Cart::forgetCurrentCart();

        return $this->withSuccess($request, [
            'message' => __('Checkout Complete!'),
        ]);
    }
}
