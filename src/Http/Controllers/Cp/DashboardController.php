<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Models\Customer;
use Damcclean\Commerce\Models\Order;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function __invoke()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
        ]);

        $orders = Order::all()
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function ($order) {
                return array_merge($order->toArray(), [
                    'edit_url' => cp_route('orders.edit', ['order' => $order->uid]),
                ]);
            });

        $customers = Customer::all()
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function ($customer) {
                return array_merge($customer->toArray(), [
                    'edit_url' => cp_route('customers.edit', ['customer' => $customer->uid]),
                ]);
            });

        return view('commerce::cp.dashboard', [
            'orders' => $orders->toArray(),
            'customers' => $customers->toArray(),
            'crumbs' => $crumbs,
        ]);
    }
}
