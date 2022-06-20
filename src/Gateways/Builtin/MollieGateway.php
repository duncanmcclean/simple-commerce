<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Gateways\Prepare;
use DoubleThreeDigital\SimpleCommerce\Gateways\Response;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus;
use Statamic\Facades\Site;
use Statamic\Statamic;

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

        $payment = $this->mollie->payments->create([
            'amount' => [
                'currency' => Currency::get(Site::current())['code'],
                'value'    => (string) substr_replace($order->grandTotal(), '.', -2, 0),
            ],
            'description' => "Order {$order->get('title')}",
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
        ], $payment->getCheckoutUrl());
    }

    public function getCharge(Order $order): Response
    {
        $this->setupMollie();

        $payment = $this->mollie->payments->get($order->gateway()['data']['id']);

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
            'authorizedAt'                    => $payment->authorizedAt,
            'expiredAt'                       => $payment->expiredAt,
            'customerId'                      => $payment->customerId,
            'countryCode'                     => $payment->countryCode,
        ]);
    }

    public function refundCharge(Order $order): Response
    {
        $this->setupMollie();

        $payment = $this->mollie->payments->get($order->gateway()['data']['id']);
        $payment->refund([]);

        return new Response(true, []);
    }

    public function callback(Request $request): bool
    {
        sleep(2);

        $order = OrderFacade::find($request->input('_order_id'));

        return $order->isPaid();
    }

    public function webhook(Request $request)
    {
        $this->setupMollie();
        $mollieId = $request->get('id');

        $payment = $this->mollie->payments->get($mollieId);

        if ($payment->status === PaymentStatus::STATUS_PAID) {
            $order = null;

            if (isset(SimpleCommerce::orderDriver()['collection'])) {
                // TODO: refactor this query
                $order = collect(OrderFacade::all())
                    ->filter(function ($entry) use ($mollieId) {
                        return isset($entry->data()->get('mollie')['id'])
                            && $entry->data()->get('mollie')['id']
                            === $mollieId;
                    })
                    ->map(function ($entry) {
                        return OrderFacade::find($entry->id());
                    })
                    ->first();
            }

            if (isset(SimpleCommerce::orderDriver()['model'])) {
                $order = (new (SimpleCommerce::orderDriver()['model']))
                    ->query()
                    ->where('data->mollie->id', $mollieId)
                    ->first();

                $order = OrderFacade::find($order->id);
            }

            if (! $order) {
                throw new OrderNotFound("Order related to Mollie transaction [{$mollieId}] could not be found.");
            }

            if ($order->isPaid() === true) {
                return;
            }

            $this->markOrderAsPaid($order);
        }
    }

    public function isOffsiteGateway(): bool
    {
        return true;
    }

    public function paymentDisplay($value): array
    {
        if (! isset($value['data']['id'])) {
            return ['text' => 'Unknown', 'url' => null];
        }

        $this->setupMollie();

        $molliePayment = $value['data']['id'];
        $mollieOrganisation = Cache::get('SimpleCommerce::MollieGateway::OrganisationId');

        return [
            'text' => $molliePayment,
            'url' => "https://www.mollie.com/dashboard/{$mollieOrganisation}/payments/{$molliePayment}",
        ];
    }

    protected function setupMollie()
    {
        $this->mollie = new MollieApiClient();
        $this->mollie->setApiKey($this->config()->get('key'));

        $this->mollie->addVersionString('Statamic/' . Statamic::version());
        $this->mollie->addVersionString('SimpleCommerce/' . SimpleCommerce::version());

        Cache::rememberForever('SimpleCommerce::MollieGateway::OrganisationId', function () {
            $currentProfile = $this->mollie->profiles->getCurrent();

            $profileDashboardUrl = $currentProfile->_links->dashboard->href;

            return explode('/', parse_url($profileDashboardUrl, PHP_URL_PATH))[2];
        });
    }
}
