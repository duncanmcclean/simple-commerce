<?php

namespace Tests\Feature\Payments;

use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as OrderContract;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Orders\OrderStatus;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\Dummy;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('payments')]
class DummyTest extends TestCase
{
    #[Test]
    public function it_can_process_a_payment()
    {
        $order = $this->makeOrder();

        (new Dummy)->process($order);

        $this->assertEquals('payment_received', $order->fresh()->status()->value);
    }

    #[Test]
    public function it_can_refund_an_order()
    {
        $order = $this->makeOrder();

        (new Dummy)->refund($order, 500);

        $this->assertEquals(500, $order->fresh()->get('amount_refunded'));
    }

    private function makeOrder(): OrderContract
    {
        $order = Order::make()
            ->status(OrderStatus::PaymentPending)
            ->grandTotal(1000)
            ->lineItems([
                ['product' => 'product-id', 'quantity' => 1, 'total' => 1000],
            ]);

        $order->save();

        return $order;
    }
}
