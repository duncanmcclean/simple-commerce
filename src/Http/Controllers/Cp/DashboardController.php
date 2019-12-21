<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Customer;
use Damcclean\Commerce\Facades\Order;
use Statamic\Extend\Management\WidgetLoader;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function __invoke(WidgetLoader $loader)
    {
        $orders = Order::all()
            ->sortByDesc('order_date')
            ->take(5);

        $customers = Customer::all()
            ->sortByDesc('customer_since')
            ->take(5);

        return view('commerce::cp.dashboard', [
            'orders' => $orders->toArray(),
            'customers' => $customers->toArray(),
        ]);
    }
}
