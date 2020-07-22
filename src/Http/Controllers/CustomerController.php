<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerController extends BaseActionController
{
    public function index(Request $request, $customer)
    {
        return Customer::find($customer)->toArray();
    }

    public function update(Request $request, $customer)
    {
        Customer::find($customer)
            ->update(Arr::except($request->all, ['_params', '_redirect', '_token']));

        return $this->withSuccess($request);
    }
}
