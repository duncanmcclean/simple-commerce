<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

use Statamic\Facades\Collection;

trait SetupCollections
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
        return Collection::make('customers')
            ->title('Customers')
            ->sites(['default'])
            ->titleFormats([
                'default' => '{name} <{email}>',
            ])
            ->save();
    }

    public function setupOrders()
    {
        return Collection::make('orders')
            ->title('Orders')
            ->sites(['default'])
            ->titleFormats([
                'default' => '#{order_number}',
            ])
            ->save();
    }

    public function setupCoupons()
    {
        return Collection::make('coupons')
            ->title('Coupons')
            ->sites(['default'])
            ->save();
    }
}
