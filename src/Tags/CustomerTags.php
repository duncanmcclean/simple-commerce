<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use Illuminate\Support\Facades\Auth;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

class CustomerTags extends SubTag
{
    use Concerns\FormBuilder;

    public function index()
    {
        // TODO: update tag documentation
        return Customer::find($this->getParam('id'))->entry()->toAugmentedArray();
    }

    public function update()
    {
        return $this->createForm(
            route('statamic.simple-commerce.customer.update'),
            [
                'customer' => $this->getParam('id'),
            ],
            'POST'
        );
    }

    public function orders()
    {
        // TODO

        return Entry::whereCollection(config('simple-commerce.collections.orders'))
            ->where('customer', Auth::user()->id)
            ->map(function (EntriesEntry $entry) {
                return $entry->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        // TODO:

        $orderId = $this->getParam('id');

        return Entry::find($orderId)->toAugmentedArray();
    }
}
