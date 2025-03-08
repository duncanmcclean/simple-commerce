<?php

namespace Tests\Feature\Cart;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

trait CartQueryTests
{
    #[Test]
    public function can_query_columns()
    {
        Cart::make()->id('123')->grandTotal(1150)->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->saveWithoutRecalculating();
        Cart::make()->id('456')->grandTotal(3470)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->saveWithoutRecalculating();
        Cart::make()->id('789')->grandTotal(9500)->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->saveWithoutRecalculating();

        $query = Cart::query()->where('grand_total', '<', 5000)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_customers()
    {
        User::make()->id('foo')->email('foo@example.com')->save();

        Cart::make()->id('123')->grandTotal(1150)->customer('foo')->saveWithoutRecalculating();
        Cart::make()->id('456')->grandTotal(3470)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->saveWithoutRecalculating();
        Cart::make()->id('789')->grandTotal(9500)->customer('foo')->saveWithoutRecalculating();

        // Query users
        $query = Cart::query()->where('customer', 'foo')->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());

        // Query guest customers
        $query = Cart::query()->where('customer', 'guest::bar@example.com')->get();

        $this->assertCount(1, $query);
        $this->assertEquals([456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_data()
    {
        Cart::make()->id('123')->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->data(['foo' => true])->saveWithoutRecalculating();
        Cart::make()->id('456')->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->data(['foo' => false])->saveWithoutRecalculating();
        Cart::make()->id('789')->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->data(['foo' => true])->saveWithoutRecalculating();

        $query = Cart::query()->where('foo', true)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());
    }

    #[Test]
    public function can_query_line_items()
    {
        User::make()->id('foo')->email('foo@example.com')->save();

        Collection::make('products')->save();
        Entry::make()->id('one')->collection('products')->save();

        Cart::make()->id('123')->lineItems([['quantity' => 3, 'product' => 'one', 'total' => 1234]])->customer('foo')->saveWithoutRecalculating();
        Cart::make()->id('456')->lineItems([['quantity' => 1, 'product' => 'one', 'total' => 1234]])->customer('foo')->saveWithoutRecalculating();
        Cart::make()->id('789')->lineItems([['quantity' => 5, 'product' => 'one', 'total' => 1234]])->customer('foo')->saveWithoutRecalculating();

        $query = Cart::query()->whereHasLineItem(function ($query) {
            $query
                ->where('quantity', '>', 2)
                ->where('total', 1234);
        })->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());
    }
}
