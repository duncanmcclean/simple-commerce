<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

class Address
{
    protected $name;
    protected $addressLine1;
    protected $addressLine2;
    protected $city;
    protected $region;
    protected $country;
    protected $zipCode;

    public function __construct($name, $addressLine1, $addressLine2, $city, $country, $zipCode, $region = null)
    {
        $this->name         = $name;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->city         = $city;
        $this->region       = $region;
        $this->country      = $country;
        $this->zipCode      = $zipCode;
    }

    public function toArray(): array
    {
        return [
            'name'           => $this->name,
            'address_line_1' => $this->addressLine1,
            'address_line_2' => $this->addressLine2,
            'city'           => $this->city,
            'region'         => $this->region,
            'country'        => $this->country,
            'zip_code'       => $this->zipCode,
        ];
    }

    public function asString(): string
    {
        return collect($this->toArray())
            ->values()
            ->join(', ');
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function addressLine1(): ?string
    {
        return $this->addressLine1;
    }

    public function addressLine2(): ?string
    {
        return $this->addressLine2;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function region(): ?string
    {
        return $this->region;
    }

    public function country(): ?string
    {
        return $this->country;
    }

    public function zipCode(): ?string
    {
        return $this->zipCode;
    }
}
