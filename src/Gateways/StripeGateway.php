<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayResponse;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeSecretMissing;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Illuminate\Http\Request;
use Statamic\Entries\Entry;
use Statamic\Facades\Site;
use Stripe\Customer as StripeCustomer;
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
            'setup_future_usage' => 'off_session',
            'metadata' => [
                'order_id' => $cart->id,
            ],
            'receipt_email' => isset($this->config()['receipt_email']) ? $this->config()['receipt_email'] : false,
        ];

        if (isset($cart->data['email']) && $cart->data['email'] !== null) {
            $customer = Customer::findByEmail($cart->data['email']);
        } elseif (isset($cart->data['customer']) && $cart->data['customer'] !== null && is_string($cart->data['customer'])) {
            $customer = Customer::find($cart->data['customer']);
        }

        if (isset($customer->data['email'])) {
            $stripeCustomerData = [
                'name'  => $customer->has('name') ? $customer->get('name') : 'Unknown',
                'email' => $customer->get('email'),
            ];

            $stripeCustomer = StripeCustomer::create($stripeCustomerData);
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
            // return new GatewayResponse(false)
            //     ->error(__('simple-commerce::gateway.stripe.no_payment_intent_provided'));
        }

        $refund = Refund::create([
            'payment_intent' => $order->data()['gateway_data']['intent'],
        ]);

        return new GatewayResponse(true, $refund->toArray());
    }

    public function webhook(Request $request)
    {
        return null;
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
