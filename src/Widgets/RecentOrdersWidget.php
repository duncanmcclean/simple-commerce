<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Illuminate\Support\Facades\Auth;
use Statamic\Facades\User;
use Statamic\Widgets\Widget;

class RecentOrdersWidget extends Widget
{
    public function html()
    {
        if (Auth::user()->hasPermission('view orders') || Auth::user()->isSuper()) {
            $orders = Order::all()
                ->sortByDesc('created_at')
                ->take(5);
        }

        return view('simple-commerce::widgets.recent-orders', [
            'orders' => isset($orders) ? $orders : collect([]),
            'statuses' => OrderStatus::all(),
        ]);
    }
}
