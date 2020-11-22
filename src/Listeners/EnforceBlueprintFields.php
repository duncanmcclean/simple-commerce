<?php

namespace DoubleThreeDigital\SimpleCommerce\Listeners;

use Statamic\Events\EntryBlueprintFound;

class EnforceBlueprintFields
{
    public function handle(EntryBlueprintFound $event)
    {
        switch ($event->blueprint->namespace()) {
            case 'collections.'.config('simple-commerce.collections.products'):
                return $this->enforceProductFields($event);

            default:
                return;
        }
    }

    protected function enforceProductFields($event)
    {
        $event->blueprint->ensureField('price', [
            'type' => 'money',
            'display' => __('Price'),
        ], 'sidebar');
    }
}
