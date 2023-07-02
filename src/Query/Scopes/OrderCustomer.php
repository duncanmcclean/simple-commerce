<?php

namespace DoubleThreeDigital\SimpleCommerce\Query\Scopes;

use DoubleThreeDigital\SimpleCommerce\Exceptions\CustomerNotFound;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use DoubleThreeDigital\SimpleCommerce\Support\Runway;
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
                'placeholder' => 'Email',
            ],
        ];
    }

    public function apply($query, $values)
    {
        try {
            $customer = Customer::findByEmail($values['email']);
        } catch (CustomerNotFound $e) {
            return $query->where('customer', null);
        }

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
        return __('Customer :email', ['email' => $values['email']]);
    }

    public function visibleTo($key)
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $key === 'entries'
                && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $runwayResource = Runway::orderModel();

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
