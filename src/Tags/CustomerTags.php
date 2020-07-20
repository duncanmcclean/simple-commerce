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
        return User::current()->data();
    }

    public function update()
    {
        return $this->createForm(
            route('statamic.simple-commerce.customer.update'),
            [],
            'POST'
        );
    }

    public function orders()
    {
        return Entry::whereCollection(config('simple-commerce.collections.orders'))
            ->where('customer', Auth::user()->id)
            ->map(function (EntriesEntry $entry) {
                return $entry->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        $orderId = $this->getParam('id');

        return Entry::find($orderId)->toAugmentedArray();
    }
}
