<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Events\PostCheckout;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
use Statamic\Entries\Entry;
use Statamic\Facades\Site;

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
        ], $payment->getCheckoutUrl());
    }

    public function purchase(GatewayPurchase $data): GatewayResponse
    {
        return new GatewayResponse(false, []);
    }

    public function purchaseRules(): array
    {
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
            $cart = Entry::whereCollection(config('simple-commerce.collections.order'))
                ->get()
                ->filter(function ($entry) use ($mollieId) {
                    return isset($entry->data()->get('gateway_data')['id']) && $entry->data()->get('gateway_data')['id'] === $mollieId;
                })
                ->map(function ($entry) {
                    return Cart::find($entry->id);
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
