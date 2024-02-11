<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Checkout;

use Illuminate\Pipeline\Pipeline;

class CheckoutPipeline extends Pipeline
{
    protected $pipes = [
        StoreCustomerOrders::class,
        RedeemCoupon::class,
        UpdateProductStock::class,
        HandleDigitalProducts::class,
    ];
}
