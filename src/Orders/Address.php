<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Regions;

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
        $this->name = $name;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->city = $city;
        $this->country = $country;
        $this->zipCode = $zipCode;
        $this->region = $region;
    }

    public function toArray(): array
    {
        return [
            'name'           => $this->name(),
            'address_line_1' => $this->addressLine1(),
            'address_line_2' => $this->addressLine2(),
            'city'           => $this->city(),
            'region'         => $this->region(),
            'country'        => $this->country(),
            'zip_code'       => $this->zipCode(),
        ];
    }

    public function asString(): string
    {
        return collect($this->toArray())
            ->values()
            ->reject(function ($value) {
                return empty($value);
            })
            ->map(function ($value) {
                if (is_array($value)) {
                    $value = $value['name'];
                }

                return $value;
            })
            ->join(','.PHP_EOL);
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

    public function region(): ?array
    {
        if (! $this->region) {
            return null;
        }

        if ($region = Regions::find($this->region)) {
            return $region;
        }

        if ($region = Regions::findByName($this->region)) {
            return $region;
        }

        return [
            'id' => str_slug($this->region),
            'name' => $this->region,
            'country_iso' => $this->country,
        ];
    }

    public function country(): ?array
    {
        return Countries::find($this->country);
    }

    public function zipCode(): ?string
    {
        return $this->zipCode;
    }
}
