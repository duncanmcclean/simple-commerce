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
use Mollie\Api\Types\OrderStatus;
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
            $molliePayment = $this->mollie->payments->create([
                'amount'      => $this->toAmount($order->get('grand_total')),
                'description' => "Order {$order->title()}",
                'webhookUrl'  => $this->webhookUrl(),
                'metadata'    => [
                    'order_id' => $order->id,
                ],
                'redirectUrl' => $this->callbackUrl([
                    '_order_id' => $data->order()->id(),
                ]),
            ]);

            return new Response(true, [
                'id' => $molliePayment->id,
                'type' => 'payments',
            ], $molliePayment->getCheckoutUrl());
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
                'orderNumber' => $order->title(),
                'locale'      => Site::current()->locale(),
                'redirectUrl' => $this->callbackUrl(),
                'webhookUrl'  => $this->webhookUrl(),
                'amount'      => $this->toAmount($order->get('grand_total')),
                'billingAddress' => [
                    'givenName'       => $billingAddress->firstName(),
                    'familyName'      => $billingAddress->lastName(),
                    'email'           => optional($order->customer())->email() ?? 'no-email@example.com',
                    'streetAndNumber' => $billingAddress->address(),
                    'postalCode'      => $billingAddress->zipCode(),
                    'city'            => $billingAddress->city(),
                    'country'         => $billingAddress->country(),
                ],
                'lines' => $order->lineItems()
                    ->map(function ($item) use ($order) {
                        $product = Product::find($item['product']);
                        // $taxAmount = $order->get('tax_total') / $order->lineItems()->count();

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
                            'type'        => $product->isDigitalProduct() ? 'digital' : 'physical',
                            'name'        => is_null($variant) ? $product->title() : "{$product->title()} - {$variant['variant']}",
                            'quantity'    => $item['quantity'],
                            'unitPrice'   => $this->toAmount(is_null($variant) ? $product->get('price') : $variant['price']),
                            'totalAmount' => $this->toAmount($item['total']),
                            'vatRate'     => '0',
                            'vatAmount'   => $this->toAmount(0),
                        ];
                    })
                    ->merge([
                        [
                            'type'        => 'shipping_fee',
                            'name'        => 'Shipping',
                            'quantity'    => 1,
                            'unitPrice'   => $this->toAmount($order->get('shipping_total')),
                            'totalAmount' => $this->toAmount($order->get('shipping_total')),
                            'vatRate'     => '0',
                            'vatAmount'   => $this->toAmount(0),
                        ],
                        [
                            'name'        => 'Tax',
                            'quantity'    => 1,
                            'unitPrice'   => $this->toAmount($order->get('tax_total')),
                            'totalAmount' => $this->toAmount($order->get('tax_total')),
                            'vatRate'     => '0',
                            'vatAmount'   => $this->toAmount(0),
                        ],
                    ])
                    ->toArray(),
            ]);

            return new Response(true, [
                'id' => $mollieOrder->id,
                'type' => 'orders',
            ], $mollieOrder->getCheckoutUrl());
        }
    }

    public function purchase(Purchase $data): Response
    {
        throw new \Exception("Mollie is an off-site gateway. The purchase() method can't be called on off-site gateways.");
    }

    public function purchaseRules(): array
    {
        return [];
    }

    public function getCharge(Order $order): Response
    {
        $this->setupMollie();

        $gatewayData = $order->get('gateway_data');

        if ($this->isUsingPaymentsApi()) {
            $molliePayment = $this->mollie->payments->get($gatewayData['id']);

            return new Response(true, [
                'id'     => $molliePayment->id,
                'status' => $molliePayment->status,
                'amount' => $molliePayment->amount,
                '_links' => $molliePayment->_links,
            ]);
        }

        if ($this->isUsingOrdersApi()) {
            $mollieOrder = $this->mollie->orders->get($gatewayData['id']);

            return new Response(true, [
                'id'     => $mollieOrder->id,
                'status' => $mollieOrder->status,
                'amount' => $mollieOrder->amount,
                '_links' => $mollieOrder->_links,
            ]);
        }
    }

    public function refundCharge(Order $order): Response
    {
        $this->setupMollie();

        $gatewayData = $order->get('gateway_data');

        if ($this->isUsingPaymentsApi()) {
            $molliePayment = $this->mollie->payments->get($gatewayData['id']);
            $molliePayment->refund([]);
        }

        if ($this->isUsingOrdersApi()) {
            $mollieOrder = $this->mollie->payments->get($gatewayData['id']);
            $mollieOrder->refund([]);
        }

        return new Response(true, []);
    }

    public function webhook(Request $request)
    {
        $this->setupMollie();

        $mollieId = $request->id;

        if ($this->isUsingPaymentsApi()) {
            $molliePayment = $this->mollie->payments->get($mollieId);

            if ($molliePayment->status === PaymentStatus::STATUS_PAID) {
                $order = OrderFacade::query()
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

        if ($this->isUsingOrdersApi()) {
            $mollieOrder = $this->mollie->orders->get($mollieId);

            if ($mollieOrder->status === OrderStatus::STATUS_PAID) {
                $order = OrderFacade::query()
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

                // TODO: should we not pass in the entire order here?
                event(new PostCheckout($order->data));
            }
        }
    }

    protected function setupMollie()
    {
        if (! $this->isUsingPaymentsApi() && ! $this->isUsingOrdersApi()) {
            throw new \Exception("[{$this->config()->get('api')}] is not a valid API option. `orders` and `payments` are allowed.");
        }

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

    protected function toAmount(int $amount): array
    {
        $amount = $amount === 0
            ? '0.00'
            : (string) substr_replace($amount, '.', -2, 0);

        return [
            'currency' => Currency::get(Site::current())['code'],
            'value'    => $amount,
        ];
    }
}
