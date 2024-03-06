<?php

namespace DuncanMcClean\SimpleCommerce\Gateways\Builtin;

use DuncanMcClean\SimpleCommerce\Contracts\Gateway;
use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Currency;
use DuncanMcClean\SimpleCommerce\Events\OrderPaymentFailed;
use DuncanMcClean\SimpleCommerce\Exceptions\OrderNotFound;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderFacade;
use DuncanMcClean\SimpleCommerce\Gateways\BaseGateway;
use DuncanMcClean\SimpleCommerce\Orders\PaymentStatus;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus as MolliePaymentStatus;
use Statamic\Facades\Site;
use Statamic\Statamic;

class MollieGateway extends BaseGateway implements Gateway
{
    protected $mollie;

    protected static $handle = 'mollie';

    public function name(): string
    {
        return __('Mollie');
    }

    public function isOffsiteGateway(): bool
    {
        return true;
    }

    public function prepare(Request $request, Order $order): array
    {
        $this->setupMollie();

        $payment = $this->mollie->payments->create([
            'amount' => [
                'currency' => Currency::get(Site::current())['code'],
                'value' => (string) substr_replace($order->grandTotal(), '.', -2, 0),
            ],
            'description' => __('Order :orderNumber', ['orderNumber' => $order->orderNumber()]),
            'redirectUrl' => $this->callbackUrl([
                '_order_id' => $order->id(),
            ]),
            'webhookUrl' => $this->webhookUrl(),
            'metadata' => [
                'order_id' => $order->id,
            ],
        ]);

        return [
            'id' => $payment->id,
            'checkout_url' => $payment->getCheckoutUrl(),
        ];
    }

    public function refund(Order $order): array
    {
        $this->setupMollie();

        $payment = $this->mollie->payments->get($order->get('gateway')['data']['id']);
        $payment->refund([]);

        return [];
    }

    public function callback(Request $request): bool
    {
        sleep(2);

        $order = OrderFacade::find($request->input('_order_id'));

        return $order->paymentStatus() === PaymentStatus::Paid;
    }

    public function webhook(Request $request)
    {
        $this->setupMollie();
        $mollieId = $request->get('id');

        $payment = $this->mollie->payments->get($mollieId);

        if ($payment->status === MolliePaymentStatus::STATUS_PAID) {
            $order = $this->getOrderFromWebhookRequest($request);

            if (! $order) {
                throw new OrderNotFound("Order related to Mollie transaction [{$mollieId}] could not be found.");
            }

            if ($order->paymentStatus() === PaymentStatus::Paid) {
                return;
            }

            $order->gatewayData(data: (array) $payment);
            $order->save();

            $this->markOrderAsPaid($order);
        }

        if ($payment->status === MolliePaymentStatus::STATUS_FAILED) {
            $order = $this->getOrderFromWebhookRequest($request);

            if (! $order) {
                throw new OrderNotFound("Order related to Mollie transaction [{$mollieId}] could not be found.");
            }

            event(new OrderPaymentFailed($order));
        }
    }

    public function fieldtypeDisplay($value): array
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

        $this->mollie->addVersionString('Statamic/'.Statamic::version());
        $this->mollie->addVersionString('SimpleCommerce/'.SimpleCommerce::version());

        Cache::rememberForever('SimpleCommerce::MollieGateway::OrganisationId', function () {
            $currentProfile = $this->mollie->profiles->getCurrent();

            $profileDashboardUrl = $currentProfile->_links->dashboard->href;

            return explode('/', parse_url($profileDashboardUrl, PHP_URL_PATH))[2];
        });
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }

    protected function getOrderFromWebhookRequest(Request $request): ?Order
    {
        return OrderFacade::query()
            ->where('mollie->id', $request->get('id'))
            ->first();
    }
}
