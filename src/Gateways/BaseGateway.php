<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

use Illuminate\Http\Request;
use DoubleThreeDigital\SimpleCommerce\Exceptions\GatewayDoesNotSupportPurchase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseGateway
{
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
     * @var Purchase $data
     * @return Response
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
}
