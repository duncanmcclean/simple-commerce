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
            $results = Customer::all();
        } else {
            $results = Customer::all()
                ->filter(function ($item) use ($query) {
                    return false !== stristr((string) $item['name'], $query);
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
