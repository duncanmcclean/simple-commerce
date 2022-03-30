<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Customers;

use DoubleThreeDigital\SimpleCommerce\Customers\Customer as CustomersCustomer;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class CustomerTest extends TestCase
{
    /** @test */
    public function can_create()
    {
        $create = Customer::make()
            ->email('joe.smith@example.com')
            ->data([
                'name' => 'Joe Smith',
            ]);

        $create->save();

        $this->assertTrue($create instanceof CustomersCustomer);

        $this->assertNotNull($create->id());

        $this->assertSame($create->name(), 'Joe Smith');
        $this->assertSame($create->email(), 'joe.smith@example.com');
        $this->assertSame($create->get('slug'), 'joesmith-at-examplecom');
    }

    /** @test */
    public function can_create_and_ensure_customer_entry_is_published()
    {
        $create = Customer::make()
            ->email('joe.smith@example.com')
            ->data([
                'name' => 'Joe Smith',
                'published' => true,
            ]);

        $create->save();

        $this->assertTrue($create instanceof CustomersCustomer);

        $this->assertNotNull($create->id());
        $this->assertSame($create->name(), 'Joe Smith');
        $this->assertSame($create->email(), 'joe.smith@example.com');

        $this->assertTrue($create->get('published'));
    }

    /** @test */
    public function can_find_by_id()
    {
        Entry::make()
            ->collection('customers')
            ->id($id = Stache::generateId())
            ->slug('smoke-at-firecom')
            ->data([
                'name' => 'Smoke Fire',
                'email' => 'smoke@fire.com',
            ])
            ->save();

        $findByEmail = Customer::find($id);

        $this->assertTrue($findByEmail instanceof CustomersCustomer);

        $this->assertSame($findByEmail->name(), 'Smoke Fire');
        $this->assertSame($findByEmail->email(), 'smoke@fire.com');
        $this->assertSame($findByEmail->get('slug'), 'smoke-at-firecom');
    }

    /** @test */
    public function can_find_by_email()
    {
        Entry::make()
            ->collection('customers')
            ->id(Stache::generateId())
            ->slug('sam-at-whitehousegov')
            ->data([
                'name' => 'Sam Seaboarn',
                'email' => 'sam@whitehouse.gov',
            ])
            ->save();

        $findByEmail = Customer::findByEmail('sam@whitehouse.gov');

        $this->assertTrue($findByEmail instanceof CustomersCustomer);

        $this->assertSame($findByEmail->name(), 'Sam Seaboarn');
        $this->assertSame($findByEmail->email(), 'sam@whitehouse.gov');
        $this->assertSame($findByEmail->get('slug'), 'sam-at-whitehousegov');
    }
}
