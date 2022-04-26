<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaymentFailed;
use DoubleThreeDigital\SimpleCommerce\Exceptions\RefundFailed;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripePaymentIntentNotProvided;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeSecretMissing;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response as GatewayResponse;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Stripe\Customer as StripeCustomer;
use Stripe\Exception\ApiErrorException;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\Refund;
use Stripe\Stripe;

class StripeGateway extends BaseGateway implements Gateway
{
    protected bool $isUsingTestMode = false;

    public function name(): string
    {
        return 'Stripe';
    }

    public function prepare(Prepare $data): GatewayResponse
    {
        $this->setUpWithStripe();

        $order = $data->order();

        $intentData = [
            'amount'             => $order->grandTotal(),
            'currency'           => Currency::get(Site::current())['code'],
            'description'        => "Order: {$order->get('title')}",
            'setup_future_usage' => 'off_session',
        ];

        $customer = $order->customer();

        if ($customer) {
            $stripeCustomerData = [
                'name'  => $customer->name() ?? 'Unknown',
                'email' => $customer->email(),
            ];

            $stripeCustomer = StripeCustomer::create($stripeCustomerData);
            $intentData['customer'] = $stripeCustomer->id;
        }

        if ($customer && $this->config()->has('receipt_email') && $this->config()->get('receipt_email') === true) {
            $intentData['receipt_email'] = $customer->email();
        }

        if ($this->config()->has('payment_intent_data')) {
            $intentData = array_merge(
                $intentData,
                $this->config()->get('payment_intent_data')($order)
            );
        }

        // We're setting this after the rest of the payment intent data,
        // in case the developer adds their own stuff to 'metadata'.
        $intentData['metadata']['order_id'] = $order->id;

        $intent = PaymentIntent::create($intentData);

        return new GatewayResponse(true, [
            'intent'         => $intent->id,
            'client_secret'  => $intent->client_secret,
        ]);
    }

    public function purchase(Purchase $data): GatewayResponse
    {
        $this->setUpWithStripe();

        $paymentIntent = PaymentIntent::retrieve($data->stripe()['intent']);
        $paymentMethod = PaymentMethod::retrieve($data->request()->payment_method);

        if ($paymentIntent->status === 'succeeded') {
            $this->markOrderAsPaid($data->order());
        }

        return new GatewayResponse(true, [
            'id'       => $paymentMethod->id,
            'object'   => $paymentMethod->object,
            'card'     => $paymentMethod->card->toArray(),
            'customer' => $paymentMethod->customer,
            'livemode' => $paymentMethod->livemode,
            'payment_intent' => $paymentIntent->id,
        ]);
    }

    public function purchaseRules(): array
    {
        return [
            'payment_method' => 'required|string',
        ];
    }

    public function getCharge(OrderContract $order): GatewayResponse
    {
        $this->setUpWithStripe();

        $paymentIntent = null;

        if (isset($order->gateway()['data']['payment_intent'])) {
            $paymentIntent = $order->gateway()['data']['payment_intent'];
        }

        if (isset($order->get('stripe')['intent'])) {
            $paymentIntent = $order->get('stripe')['intent'];
        }

        if (! $paymentIntent) {
            throw new StripePaymentIntentNotProvided('Stripe: No Payment Intent was provided to fetch.');
        }

        $charge = PaymentIntent::retrieve($paymentIntent);

        return new GatewayResponse(true, $charge->toArray());
    }

    public function refundCharge(OrderContract $order): GatewayResponse
    {
        $this->setUpWithStripe();

        $paymentIntent = null;

        if (isset($order->gateway()['data']['payment_intent'])) {
            $paymentIntent = $order->gateway()['data']['payment_intent'];
        }

        if (isset($order->get('stripe')['intent'])) {
            $paymentIntent = $order->get('stripe')['intent'];
        }

        if (! $paymentIntent) {
            throw new RefundFailed('Stripe: No Payment Intent was provided to action a refund.');
        }

        try {
            $refund = Refund::create([
                'payment_intent' => $paymentIntent,
            ]);
        } catch (ApiErrorException $e) {
            throw new RefundFailed($e->getMessage());
        }

        return new GatewayResponse(true, $refund->toArray());
    }

    public function webhook(Request $request)
    {
        $this->setUpWithStripe();

        $payload = json_decode($request->getContent(), true);
        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        $data = $payload['data']['object'];

        if ($method === 'handlePaymentIntentSucceeded') {
            $order = Order::find($data['metadata']['order_id']);

            $order->markAsPaid();

            return new Response('Webhook handled', 200);
        }

        if ($method === 'handlePaymentIntentProcessing') {
            // Wait?..
        }

        if ($method === 'handlePaymentIntentPaymentFailed') {
            $order = Order::find($data['metadata']['order_id']);

            event(new OrderPaymentFailed($order));

            return new Response('Webhook handled', 200);
        }

        if ($method === 'handleChargeRefunded') {
            $order = Order::find($data['metadata']['order_id']);

            if (! $order->isRefunded()) {
                $order->refund($payload['data']['object']);
            }

            return new Response('Webhook handled', 200);
        }

        return new Response();
    }

    public function paymentDisplay($value): array
    {
        if (! isset($value['data']['payment_intent'])) {
            return ['text' => 'Unknown', 'url' => null];
        }

        $this->setUpWithStripe();

        $stripePaymentIntent = $value['data']['payment_intent'];

        return [
            'text' => $stripePaymentIntent,
            'url' => $this->isUsingTestMode
                ? "https://dashboard.stripe.com/test/payments/{$stripePaymentIntent}"
                : "https://dashboard.stripe.com/payments/{$stripePaymentIntent}",
        ];
    }

    protected function setUpWithStripe()
    {
        if (! $this->config()->has('secret')) {
            throw new StripeSecretMissing("Could not find your Stripe Secret. Please ensure it's added to your gateway configuration.");
        }

        Stripe::setApiKey($this->config()->get('secret'));

        Stripe::setAppInfo(
            'Simple Commerce (Statamic)',
            SimpleCommerce::version(),
            'https://statamic.com/addons/double-three-digital/simple-commerce',
            'pp_partner_Jnvy4cdwcRmxfh'
        );

        if ($version = $this->config()->has('version')) {
            Stripe::setApiVersion($version);
        }

        $this->isUsingTestMode = str_contains($this->config()->get('secret'), 'sk_test_');
    }
}
