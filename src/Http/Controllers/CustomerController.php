<?php

namespace DuncanMcClean\SimpleCommerce\Http\Controllers;

use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Http\Requests\Customer\IndexRequest;
use DuncanMcClean\SimpleCommerce\Http\Requests\Customer\UpdateRequest;
use Illuminate\Support\Arr;

class CustomerController extends BaseActionController
{
    public function index(IndexRequest $request, $customer)
    {
        return [
            'data' => Customer::find($customer)->toAugmentedArray(),
        ];
    }

    public function update(UpdateRequest $request, $customer)
    {
        $customer = Customer::find($customer);

        $customer->merge(Arr::only(
            $request->all(),
            config('simple-commerce.field_whitelist.customers')
        ));

        $customer->save();

        return $this->withSuccess($request, [
            'message' => __('Customer Updated'),
            'customer' => $customer->toAugmentedArray(),
        ]);
    }
}
