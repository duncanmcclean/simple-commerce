<?php

namespace DoubleThreeDigital\SimpleCommerce\Shipping;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BaseShippingMethod
{
    public function __construct(protected array $config = [])
    {
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

    public function name(): string
    {
        return Str::title(class_basename($this));
    }
}
