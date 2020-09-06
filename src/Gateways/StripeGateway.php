<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeNoPaymentIntentProvided;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeSecretMissing;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer as SCCustomer;
use Exception;
use Statamic\Entries\Entry;
use Statamic\Facades\Site;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway extends BaseGateway implements Gateway
{
    public function name(): string
    {
        return 'Stripe';
    }

    public function prepare(GatewayPrep $data): GatewayResponse
    {
        $this->setUpWithStripe();
        $cart = $data->cart();

        $intentData = [
            'amount'   => $cart->data['grand_total'],
            'currency' => Currency::get(Site::current())['code'],
            'description' => "Order: {$cart->title}",
            'metadata' => [
                'order_id' => $cart->id,
            ],
        ];

        if (isset($cart->data['customer'])) {
            $customer = SCCustomer::find($cart->data['customer']);

            $stripeCustomer = Customer::create([
                'name' => $customer->data['name'],
                'email' => $customer->data['email'],
            ]);

            $intentData['customer'] = $stripeCustomer->id;
        }

        $intent = PaymentIntent::create($intentData);

        return new GatewayResponse(true, [
            'intent'         => $intent->id,
            'client_secret'  => $intent->client_secret,
        ]);
    }

    public function purchase(GatewayPurchase $data): GatewayResponse
    {
        $this->setUpWithStripe();

        $paymentMethod = PaymentMethod::retrieve($data->request()->payment_method);

        return new GatewayResponse(true, [
            'id'       => $paymentMethod->id,
            'object'   => $paymentMethod->object,
            'card'     => $paymentMethod->card->toArray(),
            'customer' => $paymentMethod->customer,
            'livemode' => $paymentMethod->livemode,
        ]);
    }

    public function purchaseRules(): array
    {
        return [
            'payment_method' => 'required|string',
        ];
    }

    public function getCharge(Entry $order): GatewayResponse
    {
        $this->setUpWithStripe();

        $charge = PaymentIntent::retrieve($order->data()['gateway_data']['intent']);

        return new GatewayResponse(true, $charge->toArray());
    }

    public function refundCharge(Entry $order): GatewayResponse
    {
        $this->setUpWithStripe();

        if (! isset($data['intent'])) {
            return new GatewayResponse(false)
                ->error(__('simple-commerce::gateway.stripe.no_payment_intent_provided'));
        }

        $refund = Refund::create([
            'payment_intent' => $order->data()['gateway_data']['intent'],
        ]);

        return new GatewayResponse(true, $refund->toArray());
    }

    protected function setUpWithStripe()
    {
        if (! env('STRIPE_SECRET')) {
            throw new StripeSecretMissing(__('simple-commerce::gateways.stripe.stripe_secret_missing'));
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        if ($version = env('STRIPE_API_VERSION')) {
            Stripe::setApiVersion($version);
        }
    }
}
