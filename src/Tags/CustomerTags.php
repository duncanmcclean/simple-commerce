<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderAPI;
use DoubleThreeDigital\SimpleCommerce\Orders\Order;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;

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
        if ($this->params->get('from') === 'customer') {
            return Customer::find($this->params->customer())
                ->orders()
                ->map(function (Order $order) {
                    return $order->resource()->toAugmentedArray();
                })
                ->toArray();
        }

        return Entry::whereCollection(SimpleCommerce::orderDriver()['collection'])
            ->where('customer', $this->params->customer())
            ->map(function (EntriesEntry $entry) {
                return $entry->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        return OrderAPI::find($this->params->get('id'));
    }
}
