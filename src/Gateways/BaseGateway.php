<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

class BaseGateway
{
    protected array $config = [];

    public function config(): array
    {
        return $this->config;
    }
}
