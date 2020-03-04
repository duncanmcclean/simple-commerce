<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use Statamic\View\View;
use Stripe\Exception\AuthenticationException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway implements Gateway
{
    public function __construct()
    {
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
        } catch (AuthenticationException $e) {
            throw new \Exception('Authentication to Stripe failed. Check your API keys are valid.');
        }
    }

    public function completePurchase($data)
    {
        // TODO: Implement capture() method.
    }

    public function authorize($paymentMethod)
    {
        return PaymentMethod::retrieve($paymentMethod);
    }

    public function rules()
    {
        return [
            'payment_method' => 'required|string',
        ];
    }

    public function paymentForm()
    {
        if ($total = (new Cart())->total(request()->session()->get('commerce_cart_id'))) {
            $intent = PaymentIntent::create([
                'amount' => $total * 100,
                'currency' => (new Currency())->iso(),
            ]);
        }

        return (new View)
            ->template('commerce::gateways.stripe-payment-form')
            ->with([
                'class' => get_class($this),
                'stripeKey' => config('services.stripe.key'),
                'intent' => $intent->client_secret ?? '',
            ]);
    }

    public function refund($payment)
    {
        return Refund::create(['payment_intent' => $payment]);
    }

    public function name(): string
    {
        return 'Stripe';
    }
}
