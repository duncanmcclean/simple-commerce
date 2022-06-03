<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderAPI;
use DoubleThreeDigital\SimpleCommerce\Orders\Order;

class CustomerTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        return Customer::find($this->params->get('id'))->resource()->toAugmentedArray();
    }

    public function update()
    {
        $params = [
            'customer' => $this->params->get('id'),
        ];

        return $this->createForm(
            route('statamic.simple-commerce.customer.update'),
            $params,
            'POST'
        );
    }

    public function orders()
    {
        return Customer::find($this->params->get('customer'))
            ->orders()
            ->map(function (Order $order) {
                return $order->resource()->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        return OrderAPI::find($this->params->get('id'));
    }
}
