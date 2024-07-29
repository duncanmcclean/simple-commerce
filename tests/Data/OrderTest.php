<?php

namespace Tests\Data;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
{
    #[Test]
    public function can_get_status()
    {
        // TODO: PendingPayment & Completed states.

        $order = Order::make()->set('is_cancelled', true);
        $this->assertEquals(OrderStatus::Cancelled, $order->status());

        $order = Order::make();
        $this->assertEquals(OrderStatus::Pending, $order->status());
    }

    #[Test]
    public function can_add_line_item()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function can_update_line_item()
    {
        $this->markTestIncomplete();
    }

    #[Test]
    public function can_remove_line_item()
    {
        $this->markTestIncomplete();
    }
}