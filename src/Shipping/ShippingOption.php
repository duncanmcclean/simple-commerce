<?php

namespace DuncanMcClean\SimpleCommerce\Shipping;

use DuncanMcClean\SimpleCommerce\Contracts\Purchasable;
use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Contracts\Taxes\TaxClass;
use DuncanMcClean\SimpleCommerce\Facades;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\AugmentedData;
use Statamic\Data\HasAugmentedData;
use Statamic\Facades\Site;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class ShippingOption implements Augmentable, Purchasable
{
    use FluentlyGetsAndSets, HasAugmentedData;

    public $name;
    public $handle;
    public $price;
    public $shippingMethod;

    public static function make(ShippingMethod $shippingMethod): self
    {
        return (new self)->shippingMethod($shippingMethod);
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
        // todo
        if (config('simple-commerce.taxes.shipping_tax_behaviour') === 'highest_tax_rate') {
            dd('todo');
        }

        if (! Facades\TaxClass::find('shipping')) {
            Facades\TaxClass::make()
                ->handle('shipping')
                ->set('name', __('Shipping'))
                ->save();
        }

        return Facades\TaxClass::find('shipping');
    }

    // We're overriding Statamic's AugmentedData class because it calls the price() method on
    // the ShippingOption class before attempting to get the raw value. In order for us to
    // format the price, we need to override the price() method on the AugmentedData class.
    public function newAugmentedInstance(): Augmented
    {
        return new class($this, $this->augmentedArrayData()) extends AugmentedData
        {
            public function price()
            {
                return Money::format($this->data->price(), Site::current());
            }
        };
    }

    public function augmentedArrayData(): array
    {
        return [
            'name' => $this->name(),
            'handle' => $this->handle(),
            'price' => $this->price(),
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
