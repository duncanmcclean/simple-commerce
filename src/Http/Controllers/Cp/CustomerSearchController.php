<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Facades\Damcclean\Commerce\Models\Customer;
use Statamic\Http\Controllers\CP\CpController;

class CustomerSearchController extends CpController
{
    public function __invoke()
    {
        $results = Customer::search(request()->input('search'));

        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('customers.search'),
                'sortColumn' => 'name',
            ]
        ]);
    }
}
