<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Contracts\Gateway;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Events\OrderPaymentFailed;
use DoubleThreeDigital\SimpleCommerce\Exceptions\OrderNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\PaymentStatus;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Types\PaymentStatus as MolliePaymentStatus;
use Statamic\Facades\Site;
use Statamic\Statamic;

class MollieGateway extends BaseGateway implements Gateway
{
    protected $mollie;

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
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            // TODO: refactor this query
            return collect(OrderFacade::all())
                ->filter(function ($entry) use ($request) {
                    return isset($entry->data()->get('mollie')['id'])
                        && $entry->data()->get('mollie')['id'] === $request->get('id');
                })
                ->map(function ($entry) {
                    return OrderFacade::find($entry->id());
                })
                ->first();
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $order = (new (SimpleCommerce::orderDriver()['model']))
                ->query()
                ->where('data->mollie->id', $request->get('id'))
                ->first();

            return OrderFacade::find($order->id);
        }

        return null;
    }
}
