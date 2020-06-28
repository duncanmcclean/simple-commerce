<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

use Illuminate\Support\Facades\Auth;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

trait CustomerTags
{
    public function customer()
    {
        return User::current();
    }

    public function updateCustomer()
    {
        return $this->createForm(
            route('statamic.simple-commerce.customer.update'),
            [],
            'POST'
        );
    }

    public function orders()
    {
        return Entry::whereCollection('orders')
            ->where('customer', Auth::user()->id)
            ->map(function (EntriesEntry $entry) {
                return $entry->toAugmentedArray();
            })
            ->toArray();
    }

    public function order()
    {
        //
    }
}