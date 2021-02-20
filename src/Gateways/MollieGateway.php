<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPrep;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayPurchase;
use DoubleThreeDigital\SimpleCommerce\Data\Gateways\GatewayResponse;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Facades\Shipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
use Statamic\Entries\Entry;
use Statamic\Facades\Site;
use Statamic\Facades\Entry as EntryFacade;

class MollieGateway extends BaseGateway implements Gateway
{
    protected $mollie;

    public function name(): string
    {
        return 'Mollie';
    }

    public function prepare(GatewayPrep $data): GatewayResponse
    {
        $this->setupMollie();
        $cart = $data->cart();

        if ($this->isUsingPaymentsApi()) {
            $payment = $this->mollie->payments->create([
                'amount' => [
                    'currency' => Currency::get(Site::current())['code'],
                    'value' => (string) substr_replace($cart->data['grand_total'], '.', -2, 0),
                ],
                'description' => "Order {$cart->title}",
                'redirectUrl' => $this->callbackUrl(),
                'webhookUrl'  => $this->webhookUrl(),
                'metadata' => [
                    'order_id' => $cart->id,
                ],
            ]);

            return new GatewayResponse(true, [
                'id' => $payment->id,
                'type' => 'payments',
            ], $payment->getCheckoutUrl());
        }

        if ($this->isUsingOrdersApi()) {
            $cart = Order::find($cart->id());
            $billingAddress = $cart->billingAddress();

            if (! $billingAddress) {
                throw new \Exception("Please add a billing address to the order before attempting to create a Mollie order.");
            }

            // if (! $cart->customer()) {
            //     throw new \Exception("Please add a customer to the order before attempting to create a Mollie order.");
            // }

            $order = $this->mollie->orders->create([
                'amount' => [
                    'currency' => Currency::get(Site::current())['code'],
                    'value' => (string) substr_replace(($cart->get('grand_total') - $cart->get('tax_total')), '.', -2, 0), // TODO: we shouldn't be subtracting the tax total here
                ],
                'orderNumber' => $cart->title,
                'lines' => $cart->orderItems()
                    ->map(function ($item) use ($cart) {
                        $product = Product::find($item['product']);

                        if ($product->purchasableType() === 'variants') {
                            if (is_array($item['variant'])) {
                                $variant = $product->variantOption($item['variant']['variant']);
                            } else {
                                $variant = $product->variantOption($item['variant']);
                            }
                        } else {
                            $variant = null;
                        }

                        $taxAmount = $cart->get('tax_total') / $cart->orderItems()->count();

                        $unitPrice = 0;

                        if (is_null($variant)) {
                            $unitPrice = (int) round($product->get('price') - ($taxAmount / $item['quantity']), 2) / 100;
                        } else {
                            $unitPrice = (int) round($variant['price'] - ($taxAmount / $item['quantity']), 2) / 100;
                        }

                        // dd($unitPrice);

                        $wip = [
                            'type' => $product->get('is_digital_product', false) === true
                                ? 'digital'
                                : 'physical',
                            'name' => is_null($variant)
                                ? $product->title
                                : "$product->title - {$variant['variant']}",
                            'quantity' => $item['quantity'],
                            'unitPrice' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => is_null($variant)
                                    ? (string) substr_replace($product->get('price'), '.', -2, 0)
                                    : (string) substr_replace($variant['price'], '.', -2, 0)
                                // 'value' => (string) $unitPrice,
                            ],
                            'totalAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => $totalAmount = (string) substr_replace($item['total'], '.', -2, 0),
                            ],
                            'vatRate' => '0',
                            'vatAmount' => ['currency' => 'GBP', 'value' => '0.00'],
                            // 'vatRate' => (string) substr_replace(
                            //     collect(Config::get('simple-commerce.sites'))->get(Site::current()->handle())['tax']['rate'] * 100,
                            //     '.',
                            //     -2,
                            //     0
                            // ),
                            // 'vatAmount' => [
                            //     'currency' => Currency::get(Site::current())['code'],
                            //     'value' => (string) substr_replace($taxAmount, '.', -2, 0),
                            // ],
                        ];

                        // dd($wip);

                        return $wip;
                    })
                    ->merge([
                        [
                            'type' => 'shipping_fee',
                            'name' => 'Shipping',
                            'quantity' => 1,
                            'unitPrice' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' =>  (string) substr_replace($cart->get('shipping_total'), '.', -2, 0),
                            ],
                            'totalAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' =>  (string) substr_replace($cart->get('shipping_total'), '.', -2, 0),
                            ],
                            'vatRate' => '0',
                            'vatAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => '0.00',
                            ],
                        ],
                    ])
                    // ->dd()
                    ->toArray(),
                'billingAddress' => [
                    'givenName' => $billingAddress->name(),
                    'familyName' => $billingAddress->name(),
                    // 'email' => $cart->customer()->email(),
                    'email' => 'pr@doublethree.digital',
                    'streetAndNumber' => $billingAddress->address(),
                    'postalCode' => $billingAddress->zipCode(),
                    'city' => $billingAddress->city(),
                    'country' => $billingAddress->country(),
                ],
                'redirectUrl' => $this->callbackUrl(),
                // 'webhookUrl'  => $this->webhookUrl(),
                'locale' => 'en_US',
            ]);

            return new GatewayResponse(true, [
                'id' => $order->id,
                'type' => 'orders',
            ], $order->getCheckoutUrl());
        }

        // TODO: throw exception
    }

    public function purchase(GatewayPurchase $data): GatewayResponse
    {
        // We don't actually do anything here as Mollie is an
        // off-site gateway, so it has it's own checkout page.

        // TODO: maybe throw an exception, in the case a developer gets here?

        return new GatewayResponse(false, []);
    }

    public function purchaseRules(): array
    {
        // Mollie is off-site, therefore doesn't use the traditional
        // checkout process provided by Simple Commerce. Hence why no rules
        // are defined here.

        return [];
    }

    public function getCharge(Entry $order): GatewayResponse
    {
        $this->setupMollie();
        $cart = Cart::find($order->id());

        $payment = $this->mollie->payments->get($cart->data['gateway_data']['id']);

        return new GatewayResponse(true, [
            'id' => $payment->id,
            'mode' => $payment->mode,
            'amount' => $payment->amount,
            'settlementAmount' => $payment->settlementAmount,
            'amountRefunded' => $payment->amountRefunded,
            'amountRemaining' => $payment->amountRemaining,
            'description' => $payment->description,
            'method' => $payment->method,
            'status' => $payment->status,
            'createdAt' => $payment->createdAt,
            'paidAt' => $payment->paidAt,
            'canceledAt' => $payment->canceledAt,
            'expiresAt' => $payment->expiresAt,
            'failedAt' => $payment->failedAt,
            'profileId' => $payment->profileId,
            'sequenceType' => $payment->sequenceType,
            'redirectUrl' => $payment->redirectUrl,
            'webhookUrl' => $payment->webhookUrl,
            'mandateId' => $payment->mandateId,
            'subscriptionId' => $payment->subscriptionId,
            'orderId' => $payment->orderId,
            'settlementId' => $payment->settlementId,
            'locale' => $payment->locale,
            'metadata' => $payment->metadata,
            'details' => $payment->details,
            'restrictPaymentMethodsToCountry' => $payment->restrictPaymentMethodsToCountry,
            '_links' => $payment->_links,
            '_embedded' => $payment->_embedded,
            'isCancelable' => $payment->isCancelable,
            'amountCaptured' => $payment->amountCaptured,
            'applicationFeeAmount' => $payment->applicationFeeAmount,
            'authorizedAt' => $payment->authorizedAt,
            'expiredAt' => $payment->expiredAt,
            'customerId' => $payment->customerId,
            'countryCode' => $payment->countryCode,
        ]);
    }

    public function refundCharge(Entry $order): GatewayResponse
    {
        $this->setupMollie();
        $cart = Cart::find($order->id());

        $payment = $this->mollie->payments->get($cart->data['gateway_data']['id']);

        $refund = $payment->refund([]);

        return new GatewayResponse(true, []);
    }

    public function webhook(Request $request)
    {
        $this->setupMollie();
        $mollieId = $request->id;

        $payment = $this->mollie->payments->get($mollieId);

        if ($payment->status === PaymentStatus::STATUS_PAID) {
            $cart = EntryFacade::whereCollection(config('simple-commerce.collections.orders'))
                ->filter(function ($entry) use ($mollieId) {
                    return isset($entry->data()->get('mollie')['id'])
                        && $entry->data()->get('mollie')['id']
                        === $mollieId;
                })
                ->map(function ($entry) {
                    return Cart::find($entry->id());
                })
                ->first();

            $cart->markAsCompleted();
            event(new PostCheckout($cart->data));
        }
    }

    protected function setupMollie()
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey($this->config()['key']);
    }

    protected function isUsingPaymentsApi()
    {
        return $this->configAsCollection()->get('api', 'payments') === 'payments';
    }

    protected function isUsingOrdersApi()
    {
        return $this->configAsCollection()->get('api', 'payments') === 'orders';
    }
}
