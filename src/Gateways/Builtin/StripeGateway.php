<?php

namespace DuncanMcClean\SimpleCommerce\Gateways\Builtin;

use DuncanMcClean\SimpleCommerce\Contracts\Gateway;
use DuncanMcClean\SimpleCommerce\Contracts\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Currency;
use DuncanMcClean\SimpleCommerce\Events\OrderPaymentFailed;
use DuncanMcClean\SimpleCommerce\Exceptions\RefundFailed;
use DuncanMcClean\SimpleCommerce\Exceptions\StripeSecretMissing;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
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

    protected static $handle = 'stripe';

    public function name(): string
    {
        return __('Stripe');
    }

    public function isOffsiteGateway(): bool
    {
        return $this->inPaymentElementsMode();
    }

    public function prepare(Request $request, OrderContract $order): array
    {
        $this->setUpWithStripe();

        $intentData = [
            'amount' => $order->grandTotal(),
            'currency' => Currency::get(Site::current())['code'],
            'description' => __('Order :orderNumber', ['orderNumber' => $order->orderNumber()]),
            'setup_future_usage' => 'off_session',
        ];

        $customer = $order->customer();

        if ($customer) {
            $stripeCustomerData = [
                'name' => $customer->name() ?? __('Unknown'),
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

        if ($this->inPaymentElementsMode()) {
            $intentData['automatic_payment_methods'] = [
                'enabled' => true,
            ];
        }

        // We're setting this after the rest of the payment intent data,
        // in case the developer adds their own stuff to 'metadata'.
        $intentData['metadata']['order_id'] = $order->id;

        $intent = PaymentIntent::create($intentData);

        return [
            'intent' => $intent->id,
            'client_secret' => $intent->client_secret,
        ];
    }

    public function checkout(Request $request, OrderContract $order): array
    {
        if ($this->inPaymentElementsMode()) {
            return parent::checkout($request, $order);
        }

        $this->setUpWithStripe();

        $paymentIntent = PaymentIntent::retrieve($order->get('stripe')['intent']);
        $paymentMethod = PaymentMethod::retrieve($request->payment_method);

        if ($paymentIntent->status === 'succeeded') {
            $this->markOrderAsPaid($order);
        }

        return [
            'id' => $paymentMethod->id,
            'object' => $paymentMethod->object,
            'card' => $paymentMethod->card->toArray(),
            'customer' => $paymentMethod->customer,
            'livemode' => $paymentMethod->livemode,
            'payment_intent' => $paymentIntent->id,
        ];
    }

    public function checkoutRules(): array
    {
        return [
            'payment_method' => ['required', 'string'],
        ];
    }

    public function refund(OrderContract $order): array
    {
        $this->setUpWithStripe();

        $paymentIntent = null;

        if ($order->gatewayData()->data()->has('payment_intent')) {
            $paymentIntent = $order->gatewayData()->data()->get('payment_intent');
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

        return [
            'id' => $refund->id,
            'amount' => $refund->amount,
            'payment_intent' => $refund->payment_intent,
        ];
    }

    public function callback(Request $request): bool
    {
        if ($this->inCardElementsMode()) {
            return parent::callback($request);
        }

        $this->setUpWithStripe();

        $paymentIntent = PaymentIntent::retrieve($request->payment_intent);

        if (! $paymentIntent) {
            return false;
        }

        return $paymentIntent->status === 'succeeded';
    }

    public function webhook(Request $request)
    {
        $this->setUpWithStripe();

        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

        $data = $payload['data']['object'];

        if ($method === 'handlePaymentIntentSucceeded') {
            $order = Order::find($data['metadata']['order_id']);

            $order->gatewayData(data: ['id' => $data['id']]);
            $order->save();

            $this->markOrderAsPaid($order);

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

            if ($order->paymentStatus() !== PaymentStatus::Refunded) {
                $order->refund($payload['data']['object']);
            }

            return new Response('Webhook handled', 200);
        }

        return new Response();
    }

    public function fieldtypeDisplay($value): array
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
            'https://statamic.com/addons/duncanmcclean/simple-commerce',
            'pp_partner_Jnvy4cdwcRmxfh'
        );

        if ($version = $this->config()->has('version')) {
            Stripe::setApiVersion($version);
        }

        $this->isUsingTestMode = str_contains($this->config()->get('secret'), 'sk_test_');
    }

    protected function inCardElementsMode(): bool
    {
        return $this->config()->get('mode', 'payment_elements') === 'card_elements';
    }

    protected function inPaymentElementsMode(): bool
    {
        return $this->config()->get('mode', 'payment_elements') === 'payment_elements';
    }
}
