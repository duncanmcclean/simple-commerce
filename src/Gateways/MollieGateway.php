<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use GuzzleHttp\Client;
use Mollie\Api\MollieApiClient;
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

        return new GatewayResponse(true, [], $payment->getCheckoutUrl());
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

        // TODO: add data in the response
        return new GatewayResponse(true, []);
    }

    public function webhookUrl()
    {
        // Deal with payment complete or whatever
    }

    protected function setupMollie()
    {
        $client = new Client([
            \GuzzleHttp\RequestOptions::VERIFY => \Composer\CaBundle\CaBundle::getBundledCaBundlePath(),
            \GuzzleHttp\RequestOptions::TIMEOUT => 30, // 30 second timeout
        ]);

        $this->mollie = new MollieApiClient($client);
        $this->mollie->setApiKey($this->config()['key']);
    }
}
