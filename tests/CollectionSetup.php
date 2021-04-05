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
        return Collection::make(config('simple-commerce.collections.products'))
            ->title(__('simple-commerce::messages.default_collections.products'))
            ->pastDateBehavior('public')
            ->futureDateBehavior('private')
            ->sites(['default'])
            ->routes('/products/{slug}')
            ->save();
    }

    public function setupCustomers()
    {
        return Collection::make(config('simple-commerce.collections.customers'))
            ->title(__('simple-commerce::messages.default_collections.customers'))
            ->sites(['default'])
            ->save();
    }

    public function setupOrders()
    {
        return Collection::make(config('simple-commerce.collections.orders'))
            ->title(__('simple-commerce::messages.default_collections.orders'))
            ->sites(['default'])
            ->save();
    }

    public function setupCoupons()
    {
        return Collection::make(config('simple-commerce.collections.coupons'))
            ->title(__('simple-commerce::messages.default_collections.coupons'))
            ->sites(['default'])
            ->save();
    }
}
