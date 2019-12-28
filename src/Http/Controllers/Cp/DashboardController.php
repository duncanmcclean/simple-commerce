<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Customer;
use Damcclean\Commerce\Facades\Order;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function __invoke()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url', '/commerce'],
        ]);

        $orders = Order::all()
            ->sortByDesc('order_date')
            ->take(5)
            ->map(function ($order) {
                return array_merge($order->toArray(), [
                    'edit_url' => cp_route('orders.edit', ['order' => $order['id']]),
                ]);
            });

        $customers = Customer::all()
            ->sortByDesc('customer_since')
            ->take(5)
            ->map(function ($customer) {
                return array_merge($customer->toArray(), [
                    'edit_url' => cp_route('customers.edit', ['customer' => $customer['id']]),
                ]);
            });

        return view('commerce::cp.dashboard', [
            'orders' => $orders->toArray(),
            'customers' => $customers->toArray(),
            'crumbs' => $crumbs,
        ]);
    }
}
