<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Orders;

use DoubleThreeDigital\SimpleCommerce\Countries;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Regions;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class AddressTest extends TestCase
{
    /** @test */
    public function can_get_address_as_array()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsArray($address->toArray());

        $this->assertSame($address->toArray(), [
            'name' => 'John Smith',
            'first_name' => null,
            'last_name' => null,
            'address_line_1' => '11 Test Street',
            'address_line_2' => null,
            'city' => 'Glasgow',
            'region' => Regions::find('gb-sct'),
            'country' => Countries::find('GB'),
            'zip_code' => 'G11 222',
        ]);
    }

    /** @test */
    public function can_get_address_as_array_with_first_name_and_last_name()
    {
        $order = Order::make()
            ->merge([
                'billing_first_name' => 'John',
                'billing_last_name' => 'Doe',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsArray($address->toArray());

        $this->assertSame($address->toArray(), [
            'name' => null,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'address_line_1' => '11 Test Street',
            'address_line_2' => null,
            'city' => 'Glasgow',
            'region' => Regions::find('gb-sct'),
            'country' => Countries::find('GB'),
            'zip_code' => 'G11 222',
        ]);
    }

    /** @test */
    public function can_get_address_as_string()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString((string) $address);

        $this->assertSame((string) $address, 'John Smith,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222');
    }

    /** @test */
    public function can_get_address_as_string_with_first_name_and_last_name()
    {
        $order = Order::make()
            ->merge([
                'billing_first_name' => 'John',
                'billing_last_name' => 'Doe',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString((string) $address);

        $this->assertSame((string) $address, 'John Doe,
11 Test Street,
Glasgow,
Scotland,
United Kingdom,
G11 222');
    }

    /** @test */
    public function can_get_name()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_first_name' => null,
                'billing_last_name' => null,
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->name());
        $this->assertSame($address->name(), 'John Smith');
    }

    /** @test */
    public function can_get_first_name()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => null,
                'billing_first_name' => 'Joseph',
                'billing_last_name' => null,
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->firstName());
        $this->assertSame($address->firstName(), 'Joseph');
    }

    /** @test */
    public function can_get_last_name()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => null,
                'billing_first_name' => null,
                'billing_last_name' => 'Samuel',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->lastName());
        $this->assertSame($address->lastName(), 'Samuel');
    }

    /** @test */
    public function can_get_full_name_when_name_is_one_string()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'Joseph Samuel',
                'billing_first_name' => null,
                'billing_last_name' => null,
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->fullName());
        $this->assertSame($address->fullName(), 'Joseph Samuel');
    }

    /** @test */
    public function can_get_full_name_when_name_is_separate_first_and_last_names()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => null,
                'billing_first_name' => 'Joseph',
                'billing_last_name' => 'Matthews',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->fullName());
        $this->assertSame($address->fullName(), 'Joseph Matthews');
    }

    /** @test */
    public function can_get_address_line_1()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->addressLine1());
        $this->assertSame($address->addressLine1(), '11 Test Street');
    }

    /** @test */
    public function can_get_address_line_2()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'first_name' => null,
                'last_name' => null,
                'billing_address' => '11 Test Street',
                'billing_address_line2' => 'Cardonald',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->addressLine2());
        $this->assertSame($address->addressLine2(), 'Cardonald');
    }

    /** @test */
    public function can_get_city()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->city());
        $this->assertSame($address->city(), 'Glasgow');
    }

    /** @test */
    public function can_get_region()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

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
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsArray($address->country());

        $this->assertSame($address->country(), [
            'iso' => 'GB',
            'name' => 'United Kingdom',
        ]);
    }

    /** @test */
    public function can_get_zip_code()
    {
        $order = Order::make()
            ->merge([
                'billing_name' => 'John Smith',
                'billing_address' => '11 Test Street',
                'billing_city' => 'Glasgow',
                'billing_country' => 'GB',
                'billing_zip_code' => 'G11 222',
                'billing_region' => 'gb-sct',
            ]);

        $address = $order->billingAddress();

        $this->assertIsString($address->zipCode());
        $this->assertSame($address->zipCode(), 'G11 222');
    }
}
