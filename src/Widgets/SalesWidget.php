<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use Statamic\Widgets\Widget;
use Stripe\Order;

class SalesWidget extends Widget
{
    public function html()
    {
        return view('simple-commerce::sales-widget', [
            '30_days' => 89,
        ]);
    }
}