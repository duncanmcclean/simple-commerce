<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotSupportPurchase;
use DoubleThreeDigital\SimpleCommerce\Exceptions\PayPalDetailsMissingOnOrderException;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalCheckoutSdk\Payments\CapturesRefundRequest;
use Statamic\Facades\Site;

class PayPalGateway extends BaseGateway implements Gateway
{
    protected $paypalClient;

    public function name(): string
    {
        return 'PayPal';
    }

    public function isOffsiteGateway(): bool
    {
        return $this->config()->get('mode', 'offsite') === 'offsite';
    }

    public function prepare(Prepare $data): Response
    {
        $this->setupPayPal();

        $order = $data->order();

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'value'    => (string) substr_replace($order->grandTotal(), '.', -2, 0),
                        'currency_code' => Currency::get(Site::current())['code'],
                    ],
                    'description' => "Order {$order->get('title')}",
                    'custom_id'   => $order->id(),
                ],
            ],
            'application_context' => [
                'cancel_url' => $this->callbackUrl([
                    '_order_id' => $order->id(),
                ]),
                'return_url' => $this->callbackUrl([
                    '_order_id' => $order->id(),
                ]),
            ],
        ];

        /** @var \PayPalHttp\HttpResponse $response */
        $response = $this->paypalClient->execute($request);

        $checkoutUrl = collect($response->result->links)
            ->where('rel', 'approve')
            ->first();

        return new Response(true, [
            'result' => [
                'id'            => $response->result->id,
                'currency_code' => $response->result->purchase_units[0]->amount->currency_code,
            ],
        ], $this->isOffsiteGateway() ? $checkoutUrl->href : '');
    }

    public function purchase(Purchase $data): Response
    {
        if ($this->isOffsiteGateway()) {
            throw new GatewayDoesNotSupportPurchase('Gateway [paypal] does not support the `purchase` method.');
        }

        $this->setupPayPal();

        $request = new OrdersGetRequest($data->request()->payment_id);
        // $request->path .= 'fields=payment_source';

        /** @var \PayPalHttp\HttpResponse $response */
        $response = $this->paypalClient->execute($request);

        return new Response(
            $response->result->status === 'APPROVED',
            json_decode(json_encode($response->result), true)
        );
    }

    public function purchaseRules(): array
    {
        if ($this->isOffsiteGateway()) {
            return [];
        }

        return [
            'payment_id' => 'required|string',
        ];
    }

    public function getCharge(Order $order): Response
    {
        $this->setupPayPal();

        $paypalOrderId = $order->gateway()['data']['id'];

        if (! $paypalOrderId) {
            throw new PayPalDetailsMissingOnOrderException("Order [{$order->id()}] does not have a PayPal Order ID.");
        }

        $request = new OrdersGetRequest($paypalOrderId);

        /** @var \PayPalHttp\HttpResponse $response */
        $response = $this->paypalClient->execute($request);

        return new Response(true, json_decode(json_encode($response->result), true));
    }

    public function refundCharge(Order $order): Response
    {
        $this->setupPayPal();

        // Because PayPal is sometimes an utter pain, we don't get any captures in the
        // response. So we just have to tell the user we can't process the refund.
        if (! isset($order->gateway()['data']['purchase_units'][0]['payments']['captures'][0]['id'])) {
            return new Response(false, [
                'message' => 'Sorry, a capture ID could not be found for this order.',
            ]);
        }

        $request = new CapturesRefundRequest($order->gateway()['data']['purchase_units'][0]['payments']['captures'][0]['id']);

        /** @var \PayPalHttp\HttpResponse $response */
        $response = $this->paypalClient->execute($request);

        return new Response(true, json_decode(json_encode($response->result), true));
    }

    public function callback(Request $request): bool
    {
        $this->setupPayPal();

        $order = OrderFacade::find($request->get('_order_id'));

        if (! $order) {
            return false;
        }

        $paypalOrderId = $order->gateway()['data']['id'];

        if (! $paypalOrderId) {
            throw new PayPalDetailsMissingOnOrderException("Order [{$order->id()}] does not have a PayPal Order ID.");
        }

        $request = new OrdersGetRequest($paypalOrderId);

        /** @var \PayPalHttp\HttpResponse $response */
        $response = $this->paypalClient->execute($request);

        return $response->result->status === 'APPROVED';
    }

    public function webhook(Request $request)
    {
        $this->setupPayPal();

        $payload = json_decode($request->getContent(), true);

        if ($payload['event_type'] === 'CHECKOUT.ORDER.APPROVED') {
            $order = OrderFacade::find($payload['resource']['purchase_units'][0]['custom_id']);

            $request = new OrdersCaptureRequest($payload['resource']['id']);

            /** @var \PayPalHttp\HttpResponse $response */
            $response = $this->paypalClient->execute($request);
            $responseBody = json_decode(json_encode($response->result), true);

            if (is_null($order->customer()) && $responseBody['payer']['name'] && $responseBody['payer']['email_address']) {
                try {
                    $customer = Customer::findByEmail($responseBody['payer']['email_address']);
                } catch (CustomerNotFound $e) {
                    $customer = Customer::make()
                        ->email($responseBody['payer']['email_address'])
                        ->data([
                            'name' => $responseBody['payer']['name']['given_name'] . ' ' . $responseBody['payer']['name']['surname'],
                        ]);

                    $customer->save();
                }

                $order
                    ->customer($customer->id())
                    ->save();
            }

            if (! $order->shippingAddress() && isset($responseBody['purchase_units'][0]['shipping']['address'])) {
                $paypalShipping = $responseBody['purchase_units'][0]['shipping'];

                $order
                    ->set('shipping_name', $paypalShipping['name']['full_name'])
                    ->set('shipping_address', $paypalShipping['address']['address_line_1'])
                    ->set('shipping_city', $paypalShipping['address']['admin_area_2'])
                    ->set('shipping_region', $paypalShipping['address']['admin_area_1'])
                    ->set('shipping_country', $paypalShipping['address']['country_code'])
                    ->set('shipping_postal_code', $paypalShipping['address']['postal_code'])
                    ->save();
            }

            $order->gateway(
                array_merge(
                    $order->gateway(),
                    [
                        'data' => $responseBody,
                    ]
                )
            );

            $order->save();

            $this->markOrderAsPaid($order);
        }

        return new HttpResponse();
    }

    public function paymentDisplay($value): array
    {
        if (! isset($value['data']['result']['id'])) {
            return ['text' => 'Unknown', 'url' => null];
        }

        $payment = $value['data']['result']['id'];

        return [
            'text' => $payment,
            'url' => null,
        ];
    }

    protected function setupPayPal()
    {
        if ($this->config()->get('environment') === 'sandbox') {
            $environment = new SandboxEnvironment(
                $this->config()->get('client_id'),
                $this->config()->get('client_secret')
            );
        } else {
            $environment = new ProductionEnvironment(
                $this->config()->get('client_id'),
                $this->config()->get('client_secret')
            );
        }

        $this->paypalClient = new PayPalHttpClient($environment);
    }
}
