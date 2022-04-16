<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use DoubleThreeDigital\SimpleCommerce\Checkout\HandleStock;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotSupportPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseGateway
{
    use HandleStock;

    protected array $config = [];
    protected string $handle = '';
    protected string $webhookUrl = '';
    protected string $redirectUrl = '/';
    protected string $errorRedirectUrl = '/';
    protected string $displayName = '';

    public function __construct(array $config = [], string $handle = '', string $webhookUrl = '', string $redirectUrl = '/', string $errorRedirectUrl = '/')
    {
        $this->config = $config;
        $this->handle = $handle;
        $this->webhookUrl = $webhookUrl;
        $this->redirectUrl = $redirectUrl;
        $this->errorRedirectUrl = $errorRedirectUrl;

        $this->displayName = isset($config['display']) ? $config['display'] : $this->name();
    }

    public function config(): Collection
    {
        return collect($this->config);
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function callbackUrl(array $extraParamters = [])
    {
        $data = array_merge($extraParamters, [
            'gateway'         => $this->handle,
            '_redirect'       => $this->redirectUrl,
            '_error_redirect' => $this->errorRedirectUrl,
        ]);

        return config('app.url') . route('statamic.simple-commerce.gateways.callback', $data, false);
    }

    public function webhookUrl()
    {
        return $this->webhookUrl;
    }

    public function errorRedirectUrl()
    {
        return $this->errorRedirectUrl;
    }

    public function displayName()
    {
        return $this->displayName;
    }

    public function name(): string
    {
        return Str::title(class_basename($this));
    }

    public function callback(Request $request): bool
    {
        return true;
    }

    public function isOffsiteGateway(): bool
    {
        return false;
    }

    /**
     * Method used to complete on-site purchases.
     *
     * @var Purchase
     *
     * @return Response
     *
     * @throws GatewayDoesNotSupportPurchase
     */
    public function purchase(Purchase $data): Response
    {
        throw new GatewayDoesNotSupportPurchase("Gateway [{$this->handle}] does not support the 'purchase' method.");
    }

    /**
     * Should return any validation rules required for the gateway when submitting on-site purchases.
     *
     * @return array
     */
    public function purchaseRules(): array
    {
        return [];
    }

    /**
     * Should return any validation messages required for the gateway when submitting on-site purchases.
     *
     * @return array
     */
    public function purchaseMessages(): array
    {
        return [];
    }

    /**
     * Should return an array with text & a URL which will be displayed by the Gateway fieldtype in the CP.
     *
     * @return array
     */
    public function paymentDisplay($value): array
    {
        return [
            'text' => isset($value['data']) ? $value['data']['id'] : $value['id'],
            'url' => '#',
        ];
    }

    public function markOrderAsPaid(Order $order): bool
    {
        if ($this->isOffsiteGateway()) {
            $this->handleStock($order);
        }

        $order->markAsPaid();

        return true;
    }
}
