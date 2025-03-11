<?php

namespace DuncanMcClean\SimpleCommerce\Payments\Gateways;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order;
use DuncanMcClean\SimpleCommerce\Exceptions\PreventCheckout;
use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingOption;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
use Statamic\Contracts\Auth\User;
use Statamic\Sites\Site;
use Statamic\Statamic;
use Statamic\Support\Arr;

class Mollie extends PaymentGateway
{
    private $mollie;

    public function __construct()
    {
        $this->mollie = new MollieApiClient;
        $this->mollie->setApiKey($this->config()->get('api_key'));

        $this->mollie->addVersionString('Statamic/'.Statamic::version());
        $this->mollie->addVersionString('SimpleCommerce/'.SimpleCommerce::version());

        Cache::rememberForever('Mollie_Organisation_Id', function () {
            $currentProfile = $this->mollie->profiles->getCurrent();
            $profileDashboardUrl = $currentProfile->_links->dashboard->href;

            return explode('/', parse_url($profileDashboardUrl, PHP_URL_PATH))[2];
        });
    }

    public function setup(Cart $cart): array
    {
        if ($cart->get('mollie_payment_id')) {
            $payment = $this->mollie->payments->get($cart->get('mollie_payment_id'));

            if ($payment->metadata->cart_fingerprint === $cart->fingerprint()) {
                return ['checkout_url' => $payment->getCheckoutUrl()];
            }

            $this->mollie->payments->update($payment->id, ['description' => __('Outdated payment')]);
        }

        $mollieCustomerId = $cart->customer()?->get('mollie_customer_id');

        if (! $mollieCustomerId && $cart->customer() instanceof User) {
            $customer = $this->mollie->customers->create([
                'name' => $cart->customer()->name(),
                'email' => $cart->customer()->email(),
            ]);

            $mollieCustomerId = $customer->id;

            $cart->customer()->set('mollie_customer_id', $mollieCustomerId)->save();
        }

        $payment = $this->mollie->payments->create([
            'description' => config('app.name'),
            'amount' => $this->formatAmount(site: $cart->site(), amount: $cart->grandTotal()),
            'redirectUrl' => $this->checkoutUrl(),
            'cancelUrl' => route(config('statamic.simple-commerce.routes.checkout')),
            'webhookUrl' => $this->webhookUrl(),
            'lines' => $cart->lineItems()
                ->map(function (LineItem $lineItem) use ($cart) {
                    // Mollie expects the unit price to include taxes. However, we only apply taxes to the line item total.
                    // So, we need to do some calculations to figure out what the unit price would be including tax.
                    $unitPrice = ($lineItem->total() + $lineItem->get('discount_amount', 0)) / $lineItem->quantity();

                    return [
                        'type' => $lineItem->product()->get('type', 'physical'),
                        'description' => $lineItem->product()->get('title'),
                        'quantity' => $lineItem->quantity(),
                        'unitPrice' => $this->formatAmount(site: $cart->site(), amount: $unitPrice),
                        'discountAmount' => $lineItem->has('discount_amount')
                            ? $this->formatAmount(site: $cart->site(), amount: $lineItem->get('discount_amount'))
                            : null,
                        'totalAmount' => $this->formatAmount(site: $cart->site(), amount: $lineItem->total()),
                        'vatRate' => collect($lineItem->get('tax_breakdown'))->sum('rate'),
                        'vatAmount' => $this->formatAmount(site: $cart->site(), amount: $lineItem->taxTotal()),
                        'productUrl' => $lineItem->product()->absoluteUrl(),
                    ];
                })
                ->when($cart->shippingOption(), function ($lines, ShippingOption $shippingOption) use ($cart) {
                    return $lines->push([
                        'type' => 'shipping_fee',
                        'description' => $shippingOption->name(),
                        'quantity' => 1,
                        'unitPrice' => $this->formatAmount(site: $cart->site(), amount: $cart->shippingTotal()),
                        'totalAmount' => $this->formatAmount(site: $cart->site(), amount: $cart->shippingTotal()),
                        'vatRate' => collect($cart->get('shipping_tax_breakdown'))->sum('rate'),
                        'vatAmount' => $this->formatAmount(site: $cart->site(), amount: $cart->get('shipping_tax_total', 0)),
                    ]);
                })
                ->values()->all(),
            'billingAddress' => $cart->hasBillingAddress() ? array_filter([
                'streetAndNumber' => $cart->billingAddress()?->line1,
                'streetAdditional' => $cart->billingAddress()?->line2,
                'postalCode' => $cart->billingAddress()?->postcode,
                'city' => $cart->billingAddress()?->city,
                'country' => Arr::get($cart->billingAddress()?->country()?->data(), 'iso2'),
            ]) : null,
            'shippingAddress' => $cart->hasShippingAddress() ? array_filter([
                'streetAndNumber' => $cart->shippingAddress()?->line1,
                'streetAdditional' => $cart->shippingAddress()?->line2,
                'postalCode' => $cart->shippingAddress()?->postcode,
                'city' => $cart->shippingAddress()?->city,
                'country' => Arr::get($cart->shippingAddress()?->country()?->data(), 'iso2'),
            ]) : null,
            'locale' => $cart->site()->locale(),
            'metadata' => [
                'cart_id' => $cart->id(),
                'cart_fingerprint' => $cart->fingerprint(),
            ],
            'customerId' => $mollieCustomerId,
        ]);

        $cart->set('mollie_payment_id', $payment->id)->save();

        return ['checkout_url' => $payment->getCheckoutUrl()];
    }

    public function process(Order $order): void
    {
        $payment = $this->mollie->payments->get($order->get('mollie_payment_id'));

        if ($payment->status === PaymentStatus::STATUS_CANCELED) {
            throw new PreventCheckout(__('Payment was cancelled.'));
        }

        $this->mollie->payments->update($payment->id, [
            'description' => __('Order #:orderNumber', ['orderNumber' => $order->orderNumber()]),
            'metadata' => array_merge((array) $payment->metadata, [
                'order_id' => $order->id(),
                'order_number' => $order->orderNumber(),
            ]),
        ]);
    }

    public function capture(Order $order): void
    {
        throw new \Exception("Mollie automatically captures *most* payments. They don't need captured manually.");
    }

    public function cancel(Cart $cart): void
    {
        $payment = $this->mollie->payments->get($cart->get('mollie_payment_id'));

        $payment->isCancelable
            ? $this->mollie->payments->cancel($payment->id)
            : $this->refund($cart->order(), $cart->order()->grandTotal());
    }

    public function webhook(Request $request): Response
    {
        $payment = $this->mollie->payments->get($request->id);

        if ($payment->status === PaymentStatus::STATUS_CANCELED) {
            $order = Facades\Order::query()->where('mollie_payment_id', $payment->id)->first();
            $order?->delete();
        }

        if ($payment->status === PaymentStatus::STATUS_PAID) {
            $order = Facades\Order::query()->where('mollie_payment_id', $payment->id)->first();
            $order?->status(OrderStatus::PaymentReceived)->save();
        }

        if ($payment->amountRefunded) {
            $order = Facades\Order::query()->where('mollie_payment_id', $payment->id)->first();
            $order?->set('amount_refunded', (int) str_replace('.', '', $payment->amountRefunded->value))->save();
        }

        return response('Webhook received', 200);
    }

    public function refund(Order $order, int $amount): void
    {
        $payment = $this->mollie->payments->get($order->get('mollie_payment_id'));

        $this->mollie->payments->refund($payment, [
            'amount' => $this->formatAmount(site: $order->site(), amount: $amount),
        ]);
    }

    public function fieldtypeDetails(Order $order): array
    {
        return [
            __('Payment ID') => $order->get('mollie_payment_id'),
            __('Amount') => Money::format($order->grandTotal(), $order->site()),
        ];
    }

    private function formatAmount(Site $site, int $amount): array
    {
        return [
            'currency' => Str::upper($site->attribute('currency')),
            'value' => (string) number_format($amount / 100, 2, '.', ''),
        ];
    }
}
