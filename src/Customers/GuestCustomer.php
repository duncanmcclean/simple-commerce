<?php

namespace DuncanMcClean\SimpleCommerce\Customers;

use Statamic\Data\ContainsData;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class GuestCustomer
{
    use FluentlyGetsAndSets, ContainsData;

    public function id(): string
    {
        return "guest::{$this->email()}";
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

    public function email(): string
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
}