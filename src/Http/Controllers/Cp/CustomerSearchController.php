<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Customer;
use Statamic\Http\Controllers\CP\CpController;

class CustomerSearchController extends CpController
{
    public function __invoke()
    {
        $query = request()->input('search');

        if (! $query) {
            $results = Customer::all()
                ->map(function ($customer) {
                    return array_merge($customer->toArray(), [
                        'edit_url' => cp_route('customers.edit', ['customer' => $customer['id']]),
                        'delete_url' => cp_route('customers.destroy', ['customer' => $customer['id']]),
                    ]);
                });
        } else {
            $results = Customer::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['name'], $query);
                })
                ->map(function ($customer) {
                    return array_merge($customer->toArray(), [
                        'edit_url' => cp_route('customers.edit', ['customer' => $customer['id']]),
                        'delete_url' => cp_route('customers.destroy', ['customer' => $customer['id']]),
                    ]);
                });
        }

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('customers.search'),
                'sortColumn' => 'title',
            ],
        ]);
    }
}
