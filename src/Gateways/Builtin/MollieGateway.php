<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use Illuminate\Http\Request;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
use Statamic\Facades\Site;

class MollieGateway extends BaseGateway implements Gateway
{
    protected $mollie;

    public function name(): string
    {
        return 'Mollie';
    }

    public function prepare(Prepare $data): Response
    {
        $this->setupMollie();

        $order = $data->order();

        if ($this->isUsingPaymentsApi()) {
            $payment = $this->mollie->payments->create([
                'amount' => [
                    'currency' => Currency::get(Site::current())['code'],
                    'value'    => (string) substr_replace($order->data['grand_total'], '.', -2, 0),
                ],
                'description' => "Order {$order->title()}",
                'redirectUrl' => $this->callbackUrl([
                    '_order_id' => $data->order()->id(),
                ]),
                'webhookUrl'  => $this->webhookUrl(),
                'metadata'    => [
                    'order_id' => $order->id,
                ],
            ]);

            return new Response(true, [
                'id' => $payment->id,
                'type' => 'payments',
            ], $payment->getCheckoutUrl());
        }

        if ($this->isUsingOrdersApi()) {
            $billingAddress = $order->billingAddress();

            if (! $billingAddress && $order->shippingAddress()) {
                $billingAddress = $order->shippingAddress();
            }

            if (! $billingAddress) {
                throw new \Exception("Please add a billing address to the order before attempting to create a Mollie order.");
            }

            $mollieOrder = $this->mollie->orders->create([
                'amount' => [
                    'currency' => Currency::get(Site::current())['code'],
                    'value' => (string) substr_replace($order->get('grand_total'), '.', -2, 0),
                ],
                'orderNumber' => $order->title(),
                'lines' => $order->lineItems()
                    ->map(function ($item) use ($order) {
                        $product = Product::find($item['product']);
                        $taxAmount = $order->get('tax_total') / $order->lineItems()->count();

                        if ($product->purchasableType() === 'variants') {
                            if (is_array($item['variant'])) {
                                $variant = $product->variantOption($item['variant']['variant']);
                            } else {
                                $variant = $product->variantOption($item['variant']);
                            }
                        } else {
                            $variant = null;
                        }

                        return [
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
                            ],
                            'totalAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => (string) substr_replace($item['total'], '.', -2, 0),
                            ],
                            'vatRate' => '0',
                            'vatAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => '0.00'
                            ],
                        ];
                    })
                    ->merge([
                        [
                            'type' => 'shipping_fee',
                            'name' => 'Shipping',
                            'quantity' => 1,
                            'unitPrice' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' =>  (string) substr_replace($order->get('shipping_total'), '.', -2, 0),
                            ],
                            'totalAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' =>  (string) substr_replace($order->get('shipping_total'), '.', -2, 0),
                            ],
                            'vatRate' => '0',
                            'vatAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => '0.00',
                            ],
                        ],
                        [
                            'name' => 'Tax',
                            'quantity' => 1,
                            'unitPrice' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' =>  (string) substr_replace($order->get('tax_total'), '.', -2, 0),
                            ],
                            'totalAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' =>  (string) substr_replace($order->get('tax_total'), '.', -2, 0),
                            ],
                            'vatRate' => '0',
                            'vatAmount' => [
                                'currency' => Currency::get(Site::current())['code'],
                                'value' => '0.00',
                            ],
                        ],
                    ])
                    ->toArray(),
                'billingAddress' => [
                    'givenName' => $billingAddress->name(), // TODO: this should be first name
                    'familyName' => $billingAddress->name(), // TODO: this should be last name
                    'email' => is_null($order->customer())
                        ? 'no-email@example.com'
                        : $order->customer()->email(),
                    'streetAndNumber' => $billingAddress->addressLine1(),
                    'postalCode' => $billingAddress->zipCode(),
                    'city' => $billingAddress->city(),
                    'country' => $billingAddress->country(),
                ],
                'redirectUrl' => $this->callbackUrl(),
                // 'webhookUrl'  => $this->webhookUrl(),
                'locale' => 'en_US', // TODO: allow this to be configurable
            ]);

            return new Response(true, [
                'id' => $mollieOrder->id,
                'type' => 'orders',
            ], $mollieOrder->getCheckoutUrl());
        }

        throw new \Exception("[{$this->config()->get('api')}] is not a valid API option. `orders` and `payments` are allowed.");
    }

    public function purchase(Purchase $data): Response
    {
        // We don't actually do anything here as Mollie is an
        // off-site gateway, so it has it's own checkout page.

        // TODO: maybe throw an exception, in the case a developer gets here?

        return new Response(false, []);
    }

    public function purchaseRules(): array
    {
        // Mollie is off-site, therefore doesn't use the traditional
        // checkout process provided by Simple Commerce. Hence why no rules
        // are defined here.

        return [];
    }

    public function getCharge(Order $order): Response
    {
        $this->setupMollie();

        $payment = $this->mollie->payments->get($order->data['gateway_data']['id']);

        return new Response(true, [
            'id'                              => $payment->id,
            'mode'                            => $payment->mode,
            'amount'                          => $payment->amount,
            'settlementAmount'                => $payment->settlementAmount,
            'amountRefunded'                  => $payment->amountRefunded,
            'amountRemaining'                 => $payment->amountRemaining,
            'description'                     => $payment->description,
            'method'                          => $payment->method,
            'status'                          => $payment->status,
            'createdAt'                       => $payment->createdAt,
            'paidAt'                          => $payment->paidAt,
            'canceledAt'                      => $payment->canceledAt,
            'expiresAt'                       => $payment->expiresAt,
            'failedAt'                        => $payment->failedAt,
            'profileId'                       => $payment->profileId,
            'sequenceType'                    => $payment->sequenceType,
            'redirectUrl'                     => $payment->redirectUrl,
            'webhookUrl'                      => $payment->webhookUrl,
            'mandateId'                       => $payment->mandateId,
            'subscriptionId'                  => $payment->subscriptionId,
            'orderId'                         => $payment->orderId,
            'settlementId'                    => $payment->settlementId,
            'locale'                          => $payment->locale,
            'metadata'                        => $payment->metadata,
            'details'                         => $payment->details,
            'restrictPaymentMethodsToCountry' => $payment->restrictPaymentMethodsToCountry,
            '_links'                          => $payment->_links,
            '_embedded'                       => $payment->_embedded,
            'isCancelable'                    => $payment->isCancelable,
            'amountCaptured'                  => $payment->amountCaptured,
            'applicationFeeAmount'            => $payment->applicationFeeAmount,
            'authorizedAt'                    => $payment->authorizedAt,
            'expiredAt'                       => $payment->expiredAt,
            'customerId'                      => $payment->customerId,
            'countryCode'                     => $payment->countryCode,
        ]);
    }

    public function refundCharge(Order $order): Response
    {
        $this->setupMollie();

        $payment = $this->mollie->payments->get($order->data['gateway_data']['id']);
        $payment->refund([]);

        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        $this->setupMollie();
        $mollieId = $request->id;

        $payment = $this->mollie->payments->get($mollieId);

        if ($payment->status === PaymentStatus::STATUS_PAID) {
            $order = OrderFacade::all()
                ->filter(function ($entry) use ($mollieId) {
                    return isset($entry->data()->get('mollie')['id'])
                        && $entry->data()->get('mollie')['id']
                        === $mollieId;
                })
                ->map(function ($entry) {
                    return OrderFacade::find($entry->id());
                })
                ->first();

            $order->markAsPaid();

            event(new PostCheckout($order->data));
        }
    }

    protected function setupMollie()
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey($this->config()->get('key'));
    }

    protected function isUsingPaymentsApi()
    {
        return $this->config()->get('api', 'payments') === 'payments';
    }

    protected function isUsingOrdersApi()
    {
        return $this->config()->get('api', 'payments') === 'orders';
    }
}
