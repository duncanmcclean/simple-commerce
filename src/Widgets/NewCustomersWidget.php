<?php

namespace Damcclean\Commerce\Widgets;

use Damcclean\Commerce\Models\Customer;
use Statamic\Widgets\Widget;

class NewCustomersWidget extends Widget
{
    public function html()
    {
        $customers = Customer::all()
            ->sortByDesc('created_at')
            ->take(5)
            ->map(function ($customer) {
                return array_merge($customer->toArray(), [
                    'edit_url' => cp_route('customers.edit', ['customer' => $customer->uid]),
                ]);
            });

        return view('commerce::widgets.new-customers', [
            'customers' => $customers->toArray(),
        ]);
    }
}
