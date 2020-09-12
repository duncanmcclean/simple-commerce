<?php

namespace DoubleThreeDigital\SimpleCommerce\Data;

class Address
{
    public $name;
    public $address;
    public $city;
    public $country;
    public $zipCode;

    public function __construct(string $name, string $address, string $city, string $country, string $zipCode)
    {
        $this->name = $name;
        $this->address = $address;
        $this->city = $city;
        $this->country = $country;
        $this->zipCode = $zipCode;
    }

    public function toArray(): array
    {
        return [
            'name'     => $this->name,
            'address'  => $this->address,
            'city'     => $this->city,
            'country'  => $this->country,
            'zip_code' => $this->postal,
        ];
    }

    public function asString(): string
    {
        return collect($this->toArray())
            ->values()
            ->join(', ');
    }
}
