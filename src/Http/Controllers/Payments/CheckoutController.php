<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers\Payments;

use DuncanMcClean\SimpleCommerce\Events\CouponRedeemed;
use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\PaymentGateway;
use DuncanMcClean\SimpleCommerce\Http\Controllers\Concerns\ValidatesStock;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\NotFoundHttpException;

class CheckoutController
{
    use ValidatesStock;

    // todo: write (fix) tests for this - both free and paid journeys

    public function __invoke(Request $request, ?string $paymentGateway = null)
    {
        $cart = Cart::current();

        if (! $cart->isFree()) {
            $paymentGateway = PaymentGateway::find($paymentGateway);

            throw_if(! $paymentGateway, NotFoundHttpException::class);
        }

        try {
            $this->ensureCouponIsValid($cart, $request);
            $this->ensureProductsAreAvailable($cart, $request);

            throw_if(! $cart->taxableAddress(), new PreventCheckout(__('Order cannot be created without an address.')));
            throw_if(! $cart->customer(), new PreventCheckout(__('Order cannot be created without customer information.')));

            $order = Order::query()->where('cart', $cart->id())->first();

            if (! $order) {
                $order = tap(Order::makeFromCart($cart))->save();

                // TODO: Consider whether this is the correct timing for this event to be fired.
                if ($order->coupon()) {
                    event(new CouponRedeemed($order->coupon(), $order));
                }
            }

            $order->isFree()
                ? $order->status(OrderStatus::PaymentReceived)->save()
                : $paymentGateway->process($order, $request);
        } catch (PreventCheckout $e) {
            $paymentGateway->cancel($cart);

            if ($order = Order::query()->where('cart', $cart->id())->first()) {
                $order->delete();
            }

            // todo: url should be customizable
            return redirect('/checkout')->withErrors([
                'checkout' => $e->getMessage(),
            ]);
        }

        Cart::forgetCurrentCart($cart);

        // TODO: Redirect them to the configured "checkout complete" URL.
        return redirect()->signedRoute('checkout.confirmation', [
            'order_id' => $order->id(),
        ]);
    }

    private function ensureCouponIsValid($cart, Request $request): void
    {
        if (! $cart->coupon()) {
            return;
        }

        $isValid = $cart->lineItems()->every(fn (LineItem $lineItem) => $cart->coupon()->isValid($cart, $lineItem));

        if (! $isValid) {
            throw new PreventCheckout(__('The coupon code is no longer valid for the items in your cart. Please remove it to continue.'));
        }
    }

    public function ensureProductsAreAvailable($cart, Request $request): void
    {
        $cart->lineItems()->each(function (LineItem $lineItem) use ($request, $cart) {
            try {
                $this->validateStock($request, $cart, $lineItem);
            } catch (ValidationException) {
                throw new PreventCheckout(__('One or more items in your cart are no longer available.'));
            }
        });
    }
}
