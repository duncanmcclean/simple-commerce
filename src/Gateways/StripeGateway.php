<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Helpers\Cart;
use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
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

    /**
     * @param $paymentMethod
     * @return PaymentMethod
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function authorize($paymentMethod)
    {
        return PaymentMethod::retrieve($paymentMethod);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'payment_method' => 'required|string',
        ];
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function paymentForm()
    {
        $intent = PaymentIntent::create([
            'amount' => (new Cart())->total(request()->session()->get('commerce_cart_id')),
            'currency' => (new Currency())->iso(),
        ]);

        return view('commerce::gateways.stripe-payment-form', [
            'intent' => $intent,
        ]);
    }

    /**
     * @param $payment
     * @return Refund
     * @throws \Stripe\Exception\ApiErrorException
     */
    public function refund($payment)
    {
        return Refund::create(['payment_intent' => $payment]);
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'Stripe';
    }
}
