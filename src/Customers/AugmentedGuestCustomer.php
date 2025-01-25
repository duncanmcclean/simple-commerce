<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use Statamic\Data\AbstractAugmented;

class AugmentedGuestCustomer extends AbstractAugmented
{
    public function keys()
    {
        return [
            'id',
            'name',
            'email',
            ...$this->data->data()->keys()->all(),
        ];
    }
}