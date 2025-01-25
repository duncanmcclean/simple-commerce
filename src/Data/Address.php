<?php

namespace DuncanMcClean\SimpleCommerce\Data;

use Statamic\Dictionaries\Item;
use Statamic\Facades\Dictionary;

class Address
{
    public function __construct(
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $postcode = null,
        public ?string $country = null,
        public ?string $state = null,
    ) {}

    public function country(): ?Item
    {
        if (! $this->country) {
            return null;
        }

        return Dictionary::find('countries')->get($this->country);
    }
}
