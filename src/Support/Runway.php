<?php

namespace DoubleThreeDigital\SimpleCommerce\Support;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;

class Runway
{
    public static function customerModel()
    {
        $orderModel = SimpleCommerce::orderDriver()['model'];

        return \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $orderModel);
    }

    public static function orderModel()
    {
        $orderModel = SimpleCommerce::orderDriver()['model'];

        return \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $orderModel);
    }
}
