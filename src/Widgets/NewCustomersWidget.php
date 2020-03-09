<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\Widgets\Widget;

class NewCustomersWidget extends Widget
{
    public function html()
    {
        $customers = collect([]);

        if (auth()->user()->hasPermission('view customers') || auth()->user()->isSuper()) {
            $customers = Customer::all()
                ->sortByDesc('created_at')
                ->take(5);
        }

        return view('simple-commerce::widgets.new-customers', [
            'customers' => $customers,
        ]);
    }
}
