<?php

namespace Tests\Feature\Orders\Eloquent;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\Eloquent\OrderModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Statamic;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\Feature\Orders\OrderQueryTests;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use OrderQueryTests, PreventsSavingStacheItemsToDisk, RefreshDatabase;

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

        $this->app->bind('simple-commerce.orders.eloquent.line_items_model', function () {
            return config('statamic.simple-commerce.orders.line_items_model', \DuncanMcClean\SimpleCommerce\Orders\Eloquent\LineItemModel::class);
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
            'data' => ['foo' => 'bar'],
        ]);

        $model->lineItems()->create([
            'id' => '123',
            'product' => 'abc',
            'quantity' => 1,
            'unit_price' => 2500,
            'sub_total' => 2500,
            'tax_total' => 0,
            'total' => 2500,
        ]);

        $order = Order::find($model->id);

        $this->assertEquals($model->id, $order->id());
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
        $this->assertEquals($model->data, $order->data()->except('updated_at')->all());

        $this->assertEquals('123', $order->lineItems()->first()->id());
        $this->assertEquals(2500, $order->lineItems()->first()->total());
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
            ->lineItems([['id' => '123', 'product' => 'abc', 'quantity' => 1, 'total' => 2500]])
            ->data($data = ['foo' => 'bar']);

        $save = $order->save();

        $this->assertTrue($save);

        $this->assertDatabaseHas('orders', [
            'site' => 'default',
            'grand_total' => 2500,
            'data->foo' => 'bar',
        ]);

        $this->assertDatabaseHas('order_line_items', [
            'order_id' => $order->id(),
            'product' => 'abc',
            'quantity' => 1,
            'total' => 2500,
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
