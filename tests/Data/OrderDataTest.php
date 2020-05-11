<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Data;

use DoubleThreeDigital\SimpleCommerce\Data\OrderData;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class OrderDataTest extends TestCase
{
    /** @test */
    public function it_can_do_all_the_things()
    {
        $order = factory(Order::class)->create();

        $data = (new OrderData)->data($order->toArray(), $order);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('is_paid', $data);
        $this->assertArrayHasKey('items_count', $data);
        $this->assertArrayHasKey('line_items', $data);
        $this->assertArrayHasKey('customer', $data);
        $this->assertArrayHasKey('billing_address', $data);
        $this->assertArrayHasKey('shipping_address', $data);
        $this->assertArrayHasKey('order_status', $data);
        $this->assertArrayHasKey('item_total', $data);
        $this->assertArrayHasKey('shipping_total', $data);
        $this->assertArrayHasKey('tax_total', $data);
        $this->assertArrayHasKey('coupon_total', $data);
        $this->assertArrayHasKey('total', $data);
    }
}