<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use Statamic\Events\EntryBlueprintFound;
use Illuminate\Support\Str;

class AddHiddenFields
{
    public function handle(EntryBlueprintFound $event)
    {
        if (! $event->entry) {
            return $event->blueprint;
        }

        $collections = collect(config('simple-commerce.content'))->map(function ($contentType) {
            return $contentType['collection'];
        })->flip();

        $collectionType = $collections->get($event->entry->collection()->handle());

        if (! $collectionType) {
            return $event->blueprint;
        }

        $method = 'handle' . Str::studly($collectionType) . 'Collection';

        if (method_exists($this, $method)) {
            return $this->{$method}($event);
        }
    }

    protected function handleProductsCollection(EntryBlueprintFound $event)
    {
        return $event->blueprint;
    }

    protected function handleOrdersCollection(EntryBlueprintFound $event)
    {
        $event->blueprint->ensureField('receipt_url', [
            'type'    => 'receipt_url',
            'display' => 'SC Receipt URL',
        ], 'sidebar');

        return $event->blueprint;
    }

    protected function handleCustomersCollection(EntryBlueprintFound $event)
    {
        return $event->blueprint;
    }

    protected function handleCouponsCollection(EntryBlueprintFound $event)
    {
        return $event->blueprint;
    }
}
