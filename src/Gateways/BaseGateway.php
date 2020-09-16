<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

class BaseGateway
{
    protected array $config = [];
    protected string $handle = '';

    public function __construct(array $config = [], string $handle = '')
    {
        $this->config = $config;
        $this->handle = $handle;
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
        // TODO: make this a signed url
        return config('app.url').route('statamic.simple-commerce.gateways.'.$this->handle.'.callback');
    }
}
