<?php

namespace DuncanMcClean\SimpleCommerce\Tags;

use DuncanMcClean\SimpleCommerce\Customers\Concerns\AuthorizesCustomerAccess;
use DuncanMcClean\SimpleCommerce\Facades\Customer;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderAPI;
use DuncanMcClean\SimpleCommerce\Orders\Order;

class CustomerTags extends SubTag
{
    use AuthorizesCustomerAccess;
    use Concerns\FormBuilder;

    public function index()
    {
        $customer = Customer::find($this->params->get('id'));

        if (! $this->belongsToCurrentUser($customer)) {
            return [];
        }

        return $customer->resource()->toAugmentedArray();
    }

    public function update()
    {
        $params = [
            'customer' => $this->params->get('id'),
        ];

        return $this->createForm(
            route('statamic.simple-commerce.customer.update', $params),
            [],
            'POST'
        );
    }

    public function orders()
    {
        $customer = Customer::find($this->params->get('customer'));

        if (! $this->belongsToCurrentUser($customer)) {
            return [];
        }

        return $customer->orders()
            ->map(function (Order $order) {
                return $order->resource()->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        $order = OrderAPI::find($this->params->get('id'));

        if (! $order || ! $this->belongsToCurrentUser($order->customer())) {
            return [];
        }

        return $order->toAugmentedArray();
    }
}
