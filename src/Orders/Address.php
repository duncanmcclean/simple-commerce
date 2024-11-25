<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

class Address
{
    public function __construct(
        public ?string $line1 = null,
        public ?string $line2 = null,
        public ?string $city = null,
        public ?string $postcode = null,
        public ?string $country = null,
        public ?string $region = null,
    )
    {}
}
