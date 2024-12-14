<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Facades\Blueprint;
use Statamic\Fields\Value;
use Illuminate\Support\Str;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades\Site;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingMethod;

class ShippingOption implements Purchasable, Augmentable
{
    use FluentlyGetsAndSets, HasAugmentedData;

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
                    $this->handle(Str::snake($name));
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
        return $this->fluentlyGetOrSet('shippingMethod')
            ->setter(function ($shippingMethod) {
                if ($shippingMethod instanceof ShippingMethod) {
                    return $shippingMethod->handle();
                }

                return $shippingMethod;
            })
            ->args(func_get_args());
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

    public function augmentedArrayData(): array
    {
        // TODO: Ideally, when the option is augmented, a formatted price and a shipping_method array would be returned.
        // However, because it checks methods with the same name, it returns the values of those instead.

        return [
            'name' => $this->name(),
            'handle' => $this->handle(),
            'price' => $this->price,
            'shipping_method' => $this->shippingMethod(),
        ];
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'handle' => $this->handle(),
            'price' => $this->price(),
        ];
    }
}