<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Customer\IndexRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\Customer\UpdateRequest;
use Illuminate\Support\Arr;

class CustomerController extends BaseActionController
{
    public function index(IndexRequest $request, $customer)
    {
        return Customer::find($customer)->toArray();
    }

    public function update(UpdateRequest $request, $customer)
    {
        Customer::find($customer)
            ->update(Arr::except($request->all, ['_params', '_redirect', '_token']));

        return $this->withSuccess($request);
    }
}
