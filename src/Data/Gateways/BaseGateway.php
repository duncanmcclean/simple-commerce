<?php

namespace DoubleThreeDigital\SimpleCommerce\Data\Gateways;

class BaseGateway
{
    protected array $config = [];
    protected string $handle = '';
    protected string $webhookUrl = '';
    protected string $redirectUrl = '/';
    protected string $displayName = '';

    public function __construct(array $config = [], string $handle = '', string $webhookUrl = '', string $redirectUrl = '/')
    {
        $this->config = $config;
        $this->handle = $handle;
        $this->webhookUrl = $webhookUrl;
        $this->redirectUrl = $redirectUrl;
        $this->displayName = isset($config['display']) ? $config['display'] : $this->name();
    }

    public function config(): array
    {
        // TODO: convert to a collect instance

        return $this->config;
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function callbackUrl(array $extraParamters = [])
    {
        $data = array_merge($extraParamters, [
            'gateway'   => $this->handle,
            '_redirect' => $this->redirectUrl,
            '_order_id' => null,
        ]);

        return config('app.url') . route('statamic.simple-commerce.gateways.callback', $data, false);
    }

    public function webhookUrl()
    {
        return $this->webhookUrl;
    }

    public function displayName()
    {
        return $this->displayName;
    }
}
