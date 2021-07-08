<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotSupportPurchase;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Statamic\Facades\Site;
use Illuminate\Support\Str;

class PayPalGateway extends BaseGateway implements Gateway
{
    protected $paypalClient;

    public function name(): string
    {
        return 'PayPal';
    }

    public function prepare(Prepare $data): Response
    {
        $this->setupPayPal();

        $order = $data->order();

        $request = new \PayPalCheckoutSdk\Orders\OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'value'    => (string) substr_replace($order->get('grand_total'), '.', -2, 0),
                        'currency_code' => Currency::get(Site::current())['code'],
                    ],
                    'description' => "Order {$order->title()}",
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
            'result' => json_encode($response->result),
        ], $checkoutUrl->href);
    }

    public function purchase(Purchase $data): Response
    {
        // We don't actually do anything here as PayPal is an
        // off-site gateway, so it has it's own checkout page.

        throw new GatewayDoesNotSupportPurchase("Gateway [paypal] does not support the `purchase` method.");
    }

    public function purchaseRules(): array
    {
        // PayPal is off-site, therefore doesn't use the traditional
        // checkout process provided by Simple Commerce. Hence why no rules
        // are defined here.

        return [];
    }

    public function getCharge(Order $order): Response
    {
        return new Response(true, []);
    }

    public function refundCharge(Order $order): Response
    {
        return new Response(true, []);
    }

    // v2.4 TODO: Add this method to the contract
    public function callback(Request $request): bool
    {
        $this->setupPayPal();

        $order = OrderFacade::find($request->get('_order_id'));

        if (! $order) {
            return false;
        }

        $paypalOrder = json_decode($order->get('paypal')['result'], true);

        $request = new \PayPalCheckoutSdk\Orders\OrdersGetRequest($paypalOrder['id']);

        /** @var \PayPalHttp\HttpResponse $response */
        $response = $this->paypalClient->execute($request);

        return $response->result->status === 'APPROVED';
    }

    public function webhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        if ($payload['event_type'] === 'CHECKOUT.ORDER.APPROVED') {
            $order = OrderFacade::find($payload['resource']['purchase_units'][0]['custom_id']);

            $order->markAsPaid();
            event(new PostCheckout($order));
        }

        return new HttpResponse();
    }

    protected function setupPayPal()
    {
        $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment(
            $this->config()->get('client_id'),
            $this->config()->get('client_secret')
        );

        $this->paypalClient = new \PayPalCheckoutSdk\Core\PayPalHttpClient($environment);
    }
}
