<?php

namespace DoubleThreeDigital\SimpleCommerce\Data\Gateways;

class BaseGateway
{
    protected array $config = [];
    protected string $handle = '';
    protected string $webhookUrl = '';
    protected string $redirectUrl = '';

    public function __construct(array $config = [], string $handle = '', string $webhookUrl = '', string $redirectUrl = '')
    {
        $this->config = $config;
        $this->handle = $handle;
        $this->webhookUrl = $webhookUrl;
        $this->callbackUrl = $redirectUrl;
    }

    public function config(): array
    {
        return $this->config;
    }

    public function handle(): string
    {
        return $this->handle;
    }

    public function callbackUrl()
    {
        $data = [
            'gateway' => $this->handle,
        ];

        if ($this->redirectUrl) {
            $data['_redirect'] = $this->redirectUrl;
        }

        return route('statamic.simple-commerce.gateways.callback', $data);
    }

    public function webhookUrl()
    {
        return $this->webhookUrl;
    }
}
