<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use DuncanMcClean\SimpleCommerce\Countries;
use DuncanMcClean\SimpleCommerce\Regions;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Address
{
    protected static $name;

    protected static $firstName;

    protected static $lastName;

    protected static $addressLine1;

    protected static $addressLine2;

    protected static $city;

    protected static $region;

    protected static $country;

    protected static $zipCode;

    public static function from(string $addressType, $data): self
    {
        static::$name = $data->get("{$addressType}_name");
        static::$firstName = $data->get("{$addressType}_first_name");
        static::$lastName = $data->get("{$addressType}_last_name");
        static::$addressLine1 = $data->get("{$addressType}_address") ?? $data->get("{$addressType}_address_line1");
        static::$addressLine2 = $data->get("{$addressType}_address_line2");
        static::$city = $data->get("{$addressType}_city");
        static::$country = $data->get("{$addressType}_country");
        static::$zipCode = $data->get("{$addressType}_zip_code") ?? $data->get("{$addressType}_postal_code");
        static::$region = $data->get("{$addressType}_region");

        return new static;
    }

    public function fullName(): ?string
    {
        if (static::$firstName && static::$lastName) {
            return static::$firstName.' '.static::$lastName;
        }

        return static::$name;
    }

    public function name(): ?string
    {
        return static::$name;
    }

    public function firstName(): ?string
    {
        return static::$firstName;
    }

    public function lastName(): ?string
    {
        return static::$lastName;
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
            'id' => Str::slug(static::$region),
            'name' => static::$region,
            'country_iso' => static::$country,
        ];
    }

    public function country(): ?array
    {
        if (! static::$country) {
            return null;
        }

        return Countries::find(static::$country);
    }

    public function zipCode(): ?string
    {
        return static::$zipCode;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'first_name' => $this->firstName(),
            'last_name' => $this->lastName(),
            'address_line_1' => $this->addressLine1(),
            'address_line_2' => $this->addressLine2(),
            'city' => $this->city(),
            'region' => $this->region(),
            'country' => $this->country(),
            'zip_code' => $this->zipCode(),
        ];
    }

    public function __toString()
    {
        $toArray = Arr::except($this->toArray(), ['name', 'first_name', 'last_name']);
        $toArray = collect(['name' => $this->fullName()])->merge($toArray)->toArray();

        return collect($toArray)
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
}
