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
        return [
            'data' => Customer::find($customer)->toResource(),
        ];
    }

    public function update(UpdateRequest $request, $customer)
    {
        // TODO: only save validated data, not everything

        Customer::find($customer)
            ->merge(Arr::except($request->all(), [
                '_params',
                '_redirect',
                '_token',
            ]))
            ->save();

        return $this->withSuccess($request, [
            'message'  => __('simple-commerce.messages.customer_updated'),
            'customer' => Customer::find($customer)->toResource(),
        ]);
    }
}
