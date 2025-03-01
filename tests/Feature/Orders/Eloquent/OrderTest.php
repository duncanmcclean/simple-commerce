<?php

namespace Tests\Feature\Orders\Eloquent;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('statamic.simple-commerce.orders', [
            'repository' => 'eloquent',
            'model' => \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel::class,
            'table' => 'orders',
        ]);

        $this->app->bind('simple-commerce.orders.eloquent.model', function () {
            return config('statamic.simple-commerce.orders.model', \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel::class);
        });

        Statamic::repository(
            \DuncanMcClean\SimpleCommerce\Contracts\Orders\OrderRepository::class,
            \DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderRepository::class
        );
    }

    #[Test]
    public function can_find_orders()
    {
        Cart::make()->id('abc')->save();

        $model = OrderModel::create([
            'order_number' => 1234,
            'date' => now(),
            'site' => 'default',
            'cart' => 'abc',
            'status' => 'payment_pending',
            'customer' => json_encode(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']),
            'grand_total' => 2500,
            'sub_total' => 2500,
            'discount_total' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'line_items' => [['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]],
            'data' => ['foo' => 'bar'],
        ]);

        $order = Order::find($model->uuid);

        $this->assertEquals($model->uuid, $order->id());
        $this->assertEquals($model->order_number, $order->orderNumber());
        $this->assertEquals($model->date, $order->date());
        $this->assertEquals($model->site, $order->site()->handle());
        $this->assertEquals($model->cart, $order->cart());
        $this->assertEquals($model->status, $order->status());
        $this->assertEquals(json_decode($model->customer, true)['name'], $order->customer->name());
        $this->assertEquals(json_decode($model->customer, true)['email'], $order->customer->email());
        $this->assertEquals($model->grand_total, $order->grandTotal());
        $this->assertEquals($model->sub_total, $order->subTotal());
        $this->assertEquals($model->discount_total, $order->discountTotal());
        $this->assertEquals($model->tax_total, $order->taxTotal());
        $this->assertEquals($model->shipping_total, $order->shippingTotal());
        $this->assertEquals($model->data, $order->data()->all());

        $this->assertEquals('123', $order->lineItems()->first()->id());
        $this->assertEquals(2500, $order->lineItems()->first()->total());
    }

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
    public function can_save_an_order()
    {
        $order = Order::make()
            ->site('default')
            ->cart('abc')
            ->status('payment_pending')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov'])
            ->grandTotal(2500)
            ->subTotal(2500)
            ->discountTotal(0)
            ->taxTotal(0)
            ->shippingTotal(0)
            ->lineItems($lineItems = [['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $save = $order->save();

        $this->assertTrue($save);

        $this->assertDatabaseHas('orders', [
            'site' => 'default',
            'grand_total' => 2500,
            'line_items' => json_encode($lineItems),
            'data' => json_encode($data),
        ]);

        $this->assertNotNull($order->id());
        $this->assertEquals(1, $order->orderNumber());
        $this->assertNotNull($order->date());
    }

    #[Test]
    public function can_delete_an_order()
    {
        $order = Order::make()
            ->id('123')
            ->site('default')
            ->cart('abc')
            ->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@whitehouse.gov']);

        $order->save();

        $delete = $order->delete();

        $this->assertTrue($delete);

        $this->assertDatabaseMissing('orders', [
            'id' => '123',
            'site' => 'default',
            'cart' => 'abc',
        ]);
    }
}
