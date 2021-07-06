<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response as GatewayResponse;
use DoubleThreeDigital\SimpleCommerce\Exceptions\StripeSecretMissing;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
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

    public function prepare(Prepare $data): GatewayResponse
    {
        $this->setUpWithStripe();

        $order = $data->order();

        $intentData = [
            'amount'             => $order->data['grand_total'],
            'currency'           => Currency::get(Site::current())['code'],
            'description'        => "Order: {$order->title()}",
            'setup_future_usage' => 'off_session',
            'metadata'           => [
                'order_id' => $order->id,
            ],
        ];

        $customer = $order->customer();

        if ($customer && $customer->has('email')) {
            $stripeCustomerData = [
                'name'  => $customer->has('name') ? $customer->get('name') : 'Unknown',
                'email' => $customer->get('email'),
            ];

            $stripeCustomer = StripeCustomer::create($stripeCustomerData);
            $intentData['customer'] = $stripeCustomer->id;
        }

        if ($customer && $this->config()->has('receipt_email') && $this->config()->get('receipt_email') === true) {
            $intentData['receipt_email'] = $customer->email();
        }

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
            $data->order()->markAsPaid();
        }

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

    public function getCharge(OrderContract $order): GatewayResponse
    {
        $this->setUpWithStripe();

        $charge = PaymentIntent::retrieve($order->data()->get('gateway_data')['intent']);

        return new GatewayResponse(true, $charge->toArray());
    }

    public function refundCharge(OrderContract $order): GatewayResponse
    {
        $this->setUpWithStripe();

        if (!isset($data['intent'])) {
            // return new Response(false)
            //     ->error(__('simple-commerce::gateway.stripe.no_payment_intent_provided'));
        }

        $refund = Refund::create([
            'payment_intent' => $order->data()->get('gateway_data')['intent'],
        ]);

        return new GatewayResponse(true, $refund->toArray());
    }

    public function webhook(Request $request)
    {
        $this->setUpWithStripe();

        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

        if ($method === 'handlePaymentIntentSucceeded') {
            $order = Order::find($payload['metadata']['order_id']);

            $order->markAsPaid();

            return new Response('Webhook handled', 200);
        }

        if ($method === 'handlePaymentIntentPaymentFailed') {
            // Email the customer
        }

        if ($method === 'handlePaymentIntentProcessing') {
            // Wait?
        }

        if ($method === 'handlePaymentIntentAmountCapturableUpdated') {
            // Cool, thanks Stripe?
        }

        return new Response();
    }

    protected function setUpWithStripe()
    {
        if (! $this->config()->has('secret')) {
            throw new StripeSecretMissing(__('simple-commerce::messages.gateways.stripe.stripe_secret_missing'));
        }

        Stripe::setApiKey($this->config()->get('secret'));

        if ($version = $this->config()->has('version')) {
            Stripe::setApiVersion($version);
        }
    }
}
