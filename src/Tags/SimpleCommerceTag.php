<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Currencies;
use DoubleThreeDigital\SimpleCommerce\Support\Regions;
use Statamic\Tags\TagNotFoundException;
use Statamic\Tags\Tags;

class SimpleCommerceTag extends Tags
{
    protected static $handle = 'sc';
    protected static $aliases = ['simple-commerce'];

    protected $tagClasses = [
        'cart'     => CartTags::class,
        'checkout' => CheckoutTags::class,
        'coupon'   => CouponTags::class,
        'customer' => CustomerTags::class,
        'gateways' => GatewayTags::class,
        'shipping' => ShippingTags::class,
    ];

    public function wildcard(string $tag)
    {
        $tag = explode(':', $tag);

        $class = collect($this->tagClasses)
            ->map(function ($value, $key) {
                return [
                    'key'   => $key,
                    'value' => $value,
                ];
            })
            ->where('key', $tag[0])
            ->pluck('value')
            ->first();

        $method = isset($tag[1]) ? $tag[1] : 'index';

        if (! $class) {
            throw new TagNotFoundException("Tag [{$tag[0]}] could not be found.");
        }

        if (method_exists($class, $method)) {
            return (new $class($this))->{$method}();
        }

        if (method_exists($class, 'wildcard')) {
            return (new $class($this))->wildcard($method);
        }

        throw new TagNotFoundException("Tag [{$tag[0]}] could not be found.");
    }

    public function countries()
    {
        return Countries::map(function ($country) {
            return array_merge($country, [
                'regions' => Regions::findByCountry($country)->toArray(),
            ]);
        })->toArray();
    }

    public function currencies()
    {
        return Currencies::toArray();
    }

    public function regions()
    {
        return Regions::map(function ($region) {
            return array_merge($region, [
                'country' => Countries::findByRegion($region)->first(),
            ]);
        })->toArray();
    }

    public function errors()
    {
        if (!$this->hasErrors()) {
            return null;
        }

        $errors = [];

        foreach (session('errors')->getBag('default')->all() as $error) {
            $errors[]['value'] = $error;
        }

        return $this->parseLoop($errors);
    }

    public function hasErrors()
    {
        return session()->has('errors');
    }
}
