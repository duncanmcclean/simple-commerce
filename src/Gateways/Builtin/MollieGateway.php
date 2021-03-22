<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Purchase;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Illuminate\Http\Request;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;
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
        $cart = $data->cart();

        $payment = $this->mollie->payments->create([
            'amount' => [
                'currency' => Currency::get(Site::current())['code'],
                'value'    => (string) substr_replace($cart->data['grand_total'], '.', -2, 0),
            ],
            'description' => "Order {$cart->title}",
            'redirectUrl' => $this->callbackUrl([
                '_order_id' => $data->order()->id(),
            ]),
            'webhookUrl'  => $this->webhookUrl(),
            'metadata'    => [
                'order_id' => $cart->id,
            ],
        ]);

        return new Response(true, [
            'id' => $payment->id,
        ], $payment->getCheckoutUrl());
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

    public function getCharge(Entry $order): Response
    {
        $this->setupMollie();
        $cart = Cart::find($order->id());

        $payment = $this->mollie->payments->get($cart->data['gateway_data']['id']);

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

    public function refundCharge(Entry $order): Response
    {
        $this->setupMollie();
        $cart = Cart::find($order->id());

        $payment = $this->mollie->payments->get($cart->data['gateway_data']['id']);

        $refund = $payment->refund([]);

        return new Response(true, []);
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
}
