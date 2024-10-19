<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use Illuminate\Support\Str;
use Statamic\Extend\HasHandle;
use Statamic\Extend\RegistersItself;
use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingMethod as Contract;

abstract class ShippingMethod implements Contract
{
    use HasHandle, RegistersItself;

//    public function __construct(protected array $config = []) {}
//
//    public function config(?string $key = null, $fallback = null)
//    {
//        $config = collect($this->config);
//
//        return $key
//            ? $config->get($key, $fallback)
//            : $config->all();
//    }
//
//    public function setConfig(array $config)
//    {
//        $this->config = $config;
//
//        return $this;
//    }

    public function name(): string
    {
        return Str::title(class_basename($this));
    }

    public function isAvailable(Cart $cart): bool
    {
        return $this->cost($cart) !== null;
    }

    abstract function cost(Cart $cart): int;
}
