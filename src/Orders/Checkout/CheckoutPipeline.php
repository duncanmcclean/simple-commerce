<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use Illuminate\Pipeline\Pipeline;

class CheckoutPipeline extends Pipeline
{
    public static function run(Order $order, bool $offsiteCheckout = false): Order
    {
        $pipes = [
            \DoubleThreeDigital\SimpleCommerce\Orders\Checkout\StoreCustomerOrders::class,
            \DoubleThreeDigital\SimpleCommerce\Orders\Checkout\RedeemCoupon::class,
        ];

        if ($offsiteCheckout) {
            $pipes[] = \DoubleThreeDigital\SimpleCommerce\Orders\Checkout\HandleStock::class;
        }

        return app(self::class)->send($order)->through($pipes)->thenReturn();
    }
}
