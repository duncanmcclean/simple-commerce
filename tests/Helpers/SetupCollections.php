<?php

namespace DuncanMcClean\SimpleCommerce\Tests\Helpers;

use Statamic\Facades\Collection;

trait SetupCollections
{
    public function setupCollections()
    {
        $this->setupProducts();
        $this->setupCustomers();
        $this->setupOrders();
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
}
