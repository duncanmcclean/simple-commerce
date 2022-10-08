<?php

namespace DoubleThreeDigital\SimpleCommerce\Filters;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Query\Scopes\Filter;

class OrderCustomer extends Filter
{
    public static $title = 'Customer';

    public function fieldItems()
    {
        return [
            'email' => [
                'type' => 'text',
                'input_type' => 'email',
            ],
        ];
    }

    public function apply($query, $values)
    {
        $customer = Customer::findByEmail($values['email']);

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $query->where('customer', $customer->id());
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $query->where('customer_id', $customer->id());
        }

        return $query;
    }

    public function badge($values)
    {
        $products = collect($values['products'])->map(function ($productId) {
            return Product::find($productId)->get('title');
        })->join(', ');

        return "Contains Product: {$products}";
    }

    public function visibleTo($key)
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $key === 'entries'
                && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $orderModelClass = SimpleCommerce::orderDriver()['model'];
            $runwayResource = \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $orderModelClass);

            return $key === "runway_{$runwayResource->handle()}";
        }

        return false;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
