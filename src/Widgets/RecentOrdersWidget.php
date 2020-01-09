<?php

namespace Damcclean\Commerce\Widgets;

use Damcclean\Commerce\Models\Order;
use Statamic\Widgets\Widget;

class RecentOrdersWidget extends Widget
{
    public function html()
    {
        $orders = Order::all()
            ->sortByDesc('created_at')
            ->take(5);

        return view('commerce::widgets.recent-orders', [
            'orders' => $orders,
        ]);
    }
}
