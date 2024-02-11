<?php

namespace DuncanMcClean\SimpleCommerce\Support;

use DuncanMcClean\SimpleCommerce\SimpleCommerce;

class Runway
{
    public static function customerModel()
    {
        $orderModel = SimpleCommerce::customerDriver()['model'];

        return \StatamicRadPack\Runway\Runway::findResourceByModel(new $orderModel);
    }

    public static function orderModel()
    {
        $orderModel = SimpleCommerce::orderDriver()['model'];

        return \StatamicRadPack\Runway\Runway::findResourceByModel(new $orderModel);
    }
}
