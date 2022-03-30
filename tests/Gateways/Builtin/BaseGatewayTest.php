<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Gateways\Builtin;

use DoubleThreeDigital\SimpleCommerce\Events\OrderPaid;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Gateways\BaseGateway;
use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class BaseGatewayTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_mark_order_as_paid_with_offsite_gateway()
    {
        Event::fake();

        $fakeGateway = new FakeOffsiteGateway();

        $product = Product::make()
            ->price(1500)
            ->stock(10)
            ->data([
                'title' => 'Smth',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 1,
                    'total' => 1500,
                ],
            ]);

        $order->save();

        $markOrderAsPaid = $fakeGateway->markOrderAsPaid($order);

        // Assert order has been marked as paid
        $this->assertTrue($markOrderAsPaid);
        $this->assertTrue($order->fresh()->isPaid());

        Event::assertDispatched(OrderPaid::class);

        // Assert stock count has been updated
        $this->assertSame($product->fresh()->stock(), 9);
    }

    /** @test */
    public function can_mark_order_as_paid_with_onsite_gateway()
    {
        Event::fake();

        $fakeGateway = new FakeOnsiteGateway();

        $product = Product::make()
            ->price(1500)
            ->data([
                'title' => 'Smth',
            ]);

        $product->save();

        $order = Order::make()
            ->lineItems([
                [
                    'product' => $product->id(),
                    'quantity' => 1,
                    'total' => 1500,
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
