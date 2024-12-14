<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Support\Str;
use Statamic\Facades\Site;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingMethod;

class ShippingOption implements Purchasable
{
    use FluentlyGetsAndSets;

    public $name;
    public $handle;
    public $price;
    public $shippingMethod;

    public static function make(ShippingMethod $shippingMethod): self
    {
        return (new self())->shippingMethod($shippingMethod);
    }

    public function name($name = null)
    {
        return $this->fluentlyGetOrSet('name')
            ->setter(function ($name) {
                if (! $this->handle) {
                    $this->handle(Str::slug($name));
                }

                return $name;
            })
            ->args(func_get_args());
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function price($price = null)
    {
        return $this->fluentlyGetOrSet('price')->args(func_get_args());
    }

    public function shippingMethod($shippingMethod = null)
    {
        return $this->fluentlyGetOrSet('shippingMethod')->args(func_get_args());
    }

    public function purchasablePrice(): int
    {
        return $this->price;
    }

    public function purchasableTaxClass(): TaxClass
    {
        if (config('simple-commerce.taxes.shipping_tax_behaviour') === 'highest_tax_rate') {
            dd('todo');
        }

        // todo
        return Facades\TaxClass::find('shipping');
    }

    public function toArray()
    {
        return [
            'name' => $this->name(),
            'handle' => $this->handle(),
            'price' => Money::format($this->price(), Site::default()),
            'shipping_method' => $this->shippingMethod->handle(),
        ];
    }
}