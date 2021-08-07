<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Support\Regions;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class AddressTest extends TestCase
{
    /** @test */
    public function can_get_address_as_array()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsArray($address->toArray());

        $this->assertSame($address->toArray(), [
            'name' => 'John Smith',
            'address_line_1' => '11 Test Street',
            'address_line_2' => '',
            'city' => 'Glasgow',
            'region' => Regions::find('gb-sct'),
            'country' => Countries::find('GB'),
            'zip_code' => 'G11 222',
        ]);
    }

    /** @test */
    public function can_get_address_as_string()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsString($address->asString());

        $this->assertSame($address->asString(), 'John Smith,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222');
    }

    /** @test */
    public function can_get_name()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsString($address->name());
        $this->assertSame($address->name(), 'John Smith');
    }

    /** @test */
    public function can_get_address_line_1()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsString($address->addressLine1());
        $this->assertSame($address->addressLine1(), '11 Test Street');
    }

    /** @test */
    public function can_get_address_line_2()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            'Cardonald',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsString($address->addressLine2());
        $this->assertSame($address->addressLine2(), 'Cardonald');
    }

    /** @test */
    public function can_get_city()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsString($address->city());
        $this->assertSame($address->city(), 'Glasgow');
    }

    /** @test */
    public function can_get_region()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsArray($address->region());

        $this->assertSame($address->region(), [
            'id' => 'gb-sct',
            'country_iso' => 'GB',
            'name' => 'Scotland',
        ]);
    }

    /** @test */
    public function can_get_country()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsArray($address->country());

        $this->assertSame($address->country(), [
            'iso' => 'GB',
            'name' => 'United Kingdom',
        ]);
    }

    /** @test */
    public function can_get_zip_code()
    {
        $address = new Address(
            'John Smith',
            '11 Test Street',
            '',
            'Glasgow',
            'GB',
            'G11 222',
            'gb-sct'
        );

        $this->assertIsString($address->zipCode());
        $this->assertSame($address->zipCode(), 'G11 222');
    }
}
