<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Customers;

use DoubleThreeDigital\SimpleCommerce\Customers\Customer as CustomersCustomer;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CustomerTest extends TestCase
{
    /** @test */
    public function can_create()
    {
        $create = Customer::create([
            'name'  => 'Joe Smith',
            'email' => 'joe.smith@example.com',
        ]);

        $this->assertTrue($create instanceof CustomersCustomer);

        $this->assertNotNull($create->id());
        $this->assertSame($create->name(), 'Joe Smith');
        $this->assertSame($create->email(), 'joe.smith@example.com');
    }

    /** @test */
    public function can_create_and_ensure_customer_entry_is_published()
    {
        $create = Customer::create([
            'name'      => 'Joe Smith',
            'email'     => 'joe.smith@example.com',
            'published' => true,
        ]);

        $this->assertTrue($create instanceof CustomersCustomer);

        $this->assertNotNull($create->id());
        $this->assertSame($create->name(), 'Joe Smith');
        $this->assertSame($create->email(), 'joe.smith@example.com');

        $this->assertTrue($create->published);
        $this->assertTrue($create->entry()->published());
    }
}
