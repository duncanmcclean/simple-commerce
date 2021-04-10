<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Statamic\Facades\Collection;

trait CollectionSetup
{
    public function setupCollections()
    {
        $this->setupProducts();
        $this->setupCustomers();
        $this->setupOrders();
        $this->setupCoupons();
    }

    public function setupProducts()
    {
        return Collection::make('products')
            ->title('Products')
            ->pastDateBehavior('public')
            ->futureDateBehavior('private')
            ->sites(['default'])
            ->routes('/products/{slug}')
            ->save();
    }

    public function setupCustomers()
    {
        return Collection::make('Customers')
            ->title('customers')
            ->sites(['default'])
            ->save();
    }

    public function setupOrders()
    {
        return Collection::make('Orders')
            ->title('orders')
            ->sites(['default'])
            ->save();
    }

    public function setupCoupons()
    {
        return Collection::make('Coupons')
            ->title('coupons')
            ->sites(['default'])
            ->save();
    }
}
