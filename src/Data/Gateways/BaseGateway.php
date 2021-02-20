<?php

namespace DoubleThreeDigital\SimpleCommerce\Data\Gateways;

use Illuminate\Support\Collection;

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

    public function configAsCollection(): Collection
    {
        return collect($this->config);
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function callbackUrl()
    {
        $data = [
            'gateway' => $this->handle,
            '_redirect' => $this->redirectUrl,
        ];

        return route('statamic.simple-commerce.gateways.callback', $data);
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
