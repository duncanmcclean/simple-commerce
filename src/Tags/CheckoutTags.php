<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Tags\Tags;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class CheckoutTags extends Tags
{
    public $cartId;

    protected static $handle = 'checkout';

    public function stripe()
    {
        $this->createCart();

        if (! config('simple-commerce.gateways.Stripe')) {
            throw new \Exception('Please add STRIPE_KEY and STRIPE_SECRET to your .env file and add Stripe to your Payment Gateway config.');
        }

        $stripeKey = config('simple-commerce.gateways.Stripe.key');

        Stripe::setApiKey(config('simple-commerce.gateways.Stripe.secret'));
        $stripePaymentIntent = PaymentIntent::create([
            'amount' => Cart::total($this->cartId) * 100,
            'currency' => Currency::iso()
        ])->client_secret;

        return "
            <script>
                window.stripeKey = '$stripeKey';
                window.paymentIntent = '$stripePaymentIntent';
            </script>
            <script src=\"https://js.stripe.com/v3/\"></script>
        ";
    }

    protected function createCart()
    {
        if (! request()->session()->get('commerce_cart_id')) {
            request()->session()->put('commerce_cart_id', $this->cart->create());
            request()->session()->save();
        }

        $this->cartId = request()->session()->get('commerce_cart_id');
    }
}
