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
            ->take(5);

        return view('commerce::widgets.new-customers', [
            'customers' => $customers,
        ]);
    }
}
