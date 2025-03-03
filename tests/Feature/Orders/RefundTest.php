<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Actions\Refund;
use DuncanMcClean\SimpleCommerce\Events\OrderRefunded;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class RefundTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new Refund;
    }

    #[Test]
    public function refund_action_is_visible()
    {
        $order = Order::make()
            ->grandTotal(2500)
            ->data(['amount_refunded' => 0, 'payment_gateway' => 'stripe']);

        $this->assertTrue($this->action->visibleTo($order));
    }

    #[Test]
    public function refund_action_is_not_visible_when_amount_refunded_is_equal_to_grand_total()
    {
        $order = Order::make()
            ->grandTotal(2500)
            ->data(['amount_refunded' => 2500, 'payment_gateway' => 'stripe']);

        $this->assertFalse($this->action->visibleTo($order));
    }

    #[Test]
    public function refund_action_is_not_visible_when_order_does_not_have_payment_gateway()
    {
        $order = Order::make()
            ->grandTotal(2500)
            ->data(['amount_refunded' => 0, 'payment_gateway' => null]);

        $this->assertFalse($this->action->visibleTo($order));
    }

    #[Test]
    public function order_can_be_refunded()
    {
        Event::fake();

        $order = Order::make()
            ->id('foo')
            ->grandTotal(2500)
            ->data(['amount_refunded' => 0, 'payment_gateway' => 'dummy']);

        $this->action->run([$order], ['amount' => 2500]);

        Event::assertDispatched(OrderRefunded::class, function ($event) use ($order) {
            return $event->order->id() === $order->id();
        });
    }

    #[Test]
    public function order_cannot_be_refunded_when_amount_is_less_than_or_equal_to_zero()
    {
        $order = Order::make()
            ->grandTotal(2500)
            ->data(['amount_refunded' => 0, 'payment_gateway' => 'dummy']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You must enter an amount greater than 0.');

        $this->action->run([$order], ['amount' => 0]);
    }

    #[Test]
    public function order_cannot_be_refunded_when_amount_remaining_is_less_than_the_refund_amount()
    {
        $order = Order::make()
            ->grandTotal(2500)
            ->data(['amount_refunded' => 0, 'payment_gateway' => 'dummy']);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('You cannot refund more than the remaining amount.');

        $this->action->run([$order], ['amount' => 2501]);
    }
}
