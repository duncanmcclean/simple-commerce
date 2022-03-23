<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Regions;

class Address
{
    protected static $name;
    protected static $addressLine1;
    protected static $addressLine2;
    protected static $city;
    protected static $region;
    protected static $country;
    protected static $zipCode;

    public static function from(string $addressType, $data): self
    {
        static::$name = $data->get("{$addressType}_name");
        static::$addressLine1 = $data->get("{$addressType}_address") ?? $data->get("{$addressType}_address_line1");
        static::$addressLine2 = $data->get("{$addressType}_address_line2");
        static::$city = $data->get("{$addressType}_city");
        static::$country = $data->get("{$addressType}_country");
        static::$zipCode = $data->get("{$addressType}_zip_code") ?? $data->get("{$addressType}_postal_code");
        static::$region = $data->get("{$addressType}_region");

        return new static;
    }

    public function name(): ?string
    {
        return static::$name;
    }

    public function addressLine1(): ?string
    {
        return static::$addressLine1;
    }

    public function addressLine2(): ?string
    {
        return static::$addressLine2;
    }

    public function city(): ?string
    {
        return static::$city;
    }

    public function region(): ?array
    {
        if (! static::$region) {
            return null;
        }

        if ($region = Regions::find(static::$region)) {
            return $region;
        }

        if ($region = Regions::findByName(static::$region)) {
            return $region;
        }

        return [
            'id' => str_slug(static::$region),
            'name' => static::$region,
            'country_iso' => static::$country,
        ];
    }

    public function country(): ?array
    {
        return Countries::find(static::$country);
    }

    public function zipCode(): ?string
    {
        return static::$zipCode;
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

    public function __toString()
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
            ->join(',' . PHP_EOL);
    }
}
