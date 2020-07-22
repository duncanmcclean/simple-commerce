<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use Illuminate\Support\Facades\Auth;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

class CustomerTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        return Customer::find($this->getParam('id'))->entry()->toAugmentedArray();
    }

    public function update()
    {
        $params = [
            'customer' => $this->getParam('id'),
        ];

        return $this->createForm(
            route('statamic.simple-commerce.customer.update'),
            $params,
            'POST'
        );
    }

    public function orders()
    {
        return Entry::whereCollection(config('simple-commerce.collections.orders'))
            ->where('customer', $this->getParam('customer'))
            ->map(function (EntriesEntry $entry) {
                return $entry->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        $orderId = $this->getParam('id');
        $customerId = $this->getParam('customer');

        return Entry::find($orderId);
    }
}
