<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

trait OrderQueryTests
{
    #[Test]
    public function can_query_columns()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->grandTotal(1150)->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->save();
        Order::make()->id('456')->cart('def')->grandTotal(3470)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->save();
        Order::make()->id('789')->cart('ghi')->grandTotal(9500)->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->save();

        $query = Order::query()->where('grand_total', '<', 5000)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_status()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->status(OrderStatus::PaymentPending)->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->save();
        Order::make()->id('456')->cart('def')->status(OrderStatus::PaymentPending)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->save();
        Order::make()->id('789')->cart('ghi')->status(OrderStatus::Shipped)->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->save();

        $query = Order::query()->whereStatus(OrderStatus::PaymentPending)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_customers()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        User::make()->id('foo')->email('foo@example.com')->save();

        Order::make()->id('123')->cart('abc')->grandTotal(1150)->customer('foo')->save();
        Order::make()->id('456')->cart('def')->grandTotal(3470)->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->save();
        Order::make()->id('789')->cart('ghi')->grandTotal(9500)->customer('foo')->save();

        // Query users
        $query = Order::query()->where('customer', 'foo')->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());

        // Query guest customers
        $query = Order::query()->where('customer', 'guest::bar@example.com')->get();

        $this->assertCount(1, $query);
        $this->assertEquals([456], $query->map->id()->all());
    }

    #[Test]
    public function can_query_data()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        Order::make()->id('123')->cart('abc')->customer(['name' => 'Foo', 'email' => 'foo@example.com'])->data(['foo' => true])->save();
        Order::make()->id('456')->cart('def')->customer(['name' => 'Bar', 'email' => 'bar@example.com'])->data(['foo' => false])->save();
        Order::make()->id('789')->cart('ghi')->customer(['name' => 'Baz', 'email' => 'baz@example.com'])->data(['foo' => true])->save();

        $query = Order::query()->where('foo', true)->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());
    }

    #[Test]
    public function can_query_line_items()
    {
        Cart::make()->id('abc')->save();
        Cart::make()->id('def')->save();
        Cart::make()->id('ghi')->save();

        User::make()->id('foo')->email('foo@example.com')->save();

        Collection::make('products')->save();
        Entry::make()->id('one')->collection('products')->save();

        Order::make()->id('123')->cart('abc')->lineItems([['quantity' => 3, 'product' => 'one', 'total' => 1234]])->customer('foo')->save();
        Order::make()->id('456')->cart('def')->lineItems([['quantity' => 1, 'product' => 'one', 'total' => 1234]])->customer('foo')->save();
        Order::make()->id('789')->cart('ghi')->lineItems([['quantity' => 5, 'product' => 'one', 'total' => 1234]])->customer('foo')->save();

        $query = Order::query()->whereHasLineItem(function ($query) {
            $query
                ->where('quantity', '>', 2)
                ->where('total', 1234);
        })->get();

        $this->assertCount(2, $query);
        $this->assertEquals([123, 789], $query->map->id()->all());
    }
}
