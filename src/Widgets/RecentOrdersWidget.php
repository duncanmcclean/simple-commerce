<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\Widgets\Widget;

class RecentOrdersWidget extends Widget
{
    public function html()
    {
        $orders = collect([]);

        if (auth()->user()->hasPermission('view orders') || auth()->user()->isSuper()) {
            $orders = Order::all()
                ->sortByDesc('created_at')
                ->take(5);
        }

        return view('simple-commerce::widgets.recent-orders', [
            'orders' => $orders,
            'statuses' => OrderStatus::all(),
        ]);
    }
}
