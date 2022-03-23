<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;
use Statamic\Facades\Collection;

class BaseGatewayTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Collection::make('orders')->title('Order')->save();
    }

    /** @test */
    public function can_mark_order_as_paid_with_offsite_gateway()
    {
        Event::fake();

        $fakeGateway = new FakeOffsiteGateway();

        $product = Product::make()
            ->data([
                'title' => 'Smth',
                'price' => 1500,
                'stock' => 10,
            ]);

        $product->save();

        $order = Order::make()
            ->data([
                'items' => [
                    [
                        'product' => $product->id(),
                        'quantity' => 1,
                        'total' => 1500,
                    ],
                ],
            ]);

        $order->save();

        $markOrderAsPaid = $fakeGateway->markOrderAsPaid($order);

        // Assert order has been marked as paid
        $this->assertTrue($markOrderAsPaid);
        $this->assertTrue($order->fresh()->isPaid());

        Event::assertDispatched(OrderPaid::class);

        // Assert stock count has been updated
        $this->assertSame($product->fresh()->get('stock'), 9);
    }

    /** @test */
    public function can_mark_order_as_paid_with_onsite_gateway()
    {
        Event::fake();

        $fakeGateway = new FakeOnsiteGateway();

        $product = Product::make()
            ->data([
                'title' => 'Smth',
                'price' => 1500,
            ]);

        $product->save();

        $order = Order::make()
            ->data([
                'items' => [
                    [
                        'product' => $product->id(),
                        'quantity' => 1,
                        'total' => 1500,
                    ],
                ],
            ]);

        $order->save();

        $markOrderAsPaid = $fakeGateway->markOrderAsPaid($order);

        // Assert order has been marked as paid
        $this->assertTrue($markOrderAsPaid);
        $this->assertTrue($order->fresh()->isPaid());

        Event::assertDispatched(OrderPaid::class);
    }
}

class FakeOnsiteGateway extends BaseGateway
{
    public function name(): string
    {
        return 'Fake Onsite Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return false;
    }
}

class FakeOffsiteGateway extends BaseGateway
{
    public function name(): string
    {
        return 'Fake Offsite Gateway';
    }

    public function isOffsiteGateway(): bool
    {
        return true;
    }
}
