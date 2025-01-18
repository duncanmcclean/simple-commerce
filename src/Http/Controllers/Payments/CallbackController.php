<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Payments;

use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use Illuminate\Http\Request;
use Statamic\Exceptions\NotFoundHttpException;

class CallbackController
{
    public function __invoke(Request $request, string $paymentGateway)
    {
        // todo: split this into a pipeline
        // todo: merge this with the CheckoutController

        $cart = Cart::current();
        $paymentGateway = PaymentGateway::find($paymentGateway);

        throw_if(! $paymentGateway, NotFoundHttpException::class);

        if (! $cart->customer()) {
            $paymentGateway->cancel($cart);

            // todo: url should be customizable
            return redirect('/checkout')->withErrors([
                'checkout' => __('Order cannot be created without customer information.'),
            ]);
        }

        if (! $cart->taxableAddress()) {
            $paymentGateway->cancel($cart);

            // todo: url should be customizable
            return redirect('/checkout')->withErrors([
                'checkout' => __('Order cannot be created without an address.'),
            ]);
        }

        $order = Order::query()->where('cart', $cart->id())->first();

        if (! $order) {
            $order = Order::makeFromCart($cart);
            $order->save();

            if ($order->coupon()) {
                event(new CouponRedeemed($order->coupon(), $order)); // todo: consider whether this is the right timing for this event
            }
        }

        $paymentGateway->process($order, $request);

        // todo: uncomment this when we figure out how to load the *old* cart on the confirmation
        // page AND get rid of the cart when we're done with it
        //        Cart::forgetCurrentCart();

        // todo: make this configurable (how... i don't know)
        return redirect("/checkout/complete?order_id={$order->id()}");
    }
}
