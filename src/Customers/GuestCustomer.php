<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class GuestCustomer implements Augmentable
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedInstance;

    public function id(): ?string
    {
        if (! $email = $this->email()) {
            return null;
        }

        return "guest::{$email}";
    }

    public function name(): ?string
    {
        if ($name = $this->get('name')) {
            return $name;
        }

        if ($name = $this->get('first_name')) {
            if ($lastName = $this->get('last_name')) {
                $name .= ' '.$lastName;
            }

            return $name;
        }

        return $this->email();
    }

    public function email(): ?string
    {
        return $this->get('email');
    }

    public function routeNotificationForMail($notification = null)
    {
        return $this->email();
    }

    public function toArray(): array
    {
        return $this->data()->filter()->all();
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedGuestCustomer($this);
    }
}
