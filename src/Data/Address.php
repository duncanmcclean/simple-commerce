<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

class Address
{
    public $name;
    public $address;
    public $city;
    public $country;
    public $postal;

    public function __construct(string $name, string $address, string $city, string $country, string $postal)
    {
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->postal = $postal;
    }

    public function toArray(): array
    {
        return [
            'name'    => $this->name,
            'address' => $this->address,
            'city'    => $this->city,
            'country' => $this->country,
            'postal'  => $this->postal,
        ];
    }

    public function asString(): string
    {
        return collect($this->toArray())
            ->values()
            ->join(', ');
    }
}
