<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

class Address
{
    // TODO: make these protected in the future?
    // TODO: also maybe use types on these properties?
    public $name;
    public $address;
    public $city;
    public $country;
    public $zipCode;
    public $shippingNote;

    public function __construct(string $name, string $address, string $city, string $country, string $zipCode, string $shippingNote = null)
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
            'name'          => $this->name,
            'address'       => $this->address,
            'city'          => $this->city,
            'country'       => $this->country,
            'zip_code'      => $this->zipCode,
            'shipping_note' => $this->shippingNote,
        ];
    }

    public function asString(): string
    {
        return collect($this->toArray())
            ->values()
            ->join(', ');
    }

    public function name(): string
    {
        return $this->name;
    }

    public function address(): string
    {
        return $this->address;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function country(): string
    {
        return $this->country;
    }

    public function zipCode(): string
    {
        return $this->zipCode;
    }

    public function shippingNote(): ?string
    {
        return $this->shippingNote;
    }
}
