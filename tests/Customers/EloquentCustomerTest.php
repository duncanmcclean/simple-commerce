<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Customers;

use DoubleThreeDigital\SimpleCommerce\Customers\CustomerModel;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use DoubleThreeDigital\SimpleCommerce\Tests\UseDatabaseContentDrivers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;

class EloquentCustomerTest extends TestCase
{
    use RefreshDatabase, UseDatabaseContentDrivers;

    /** @test */
    public function can_get_all_customers()
    {
        CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
        ]);

        CustomerModel::create([
            'name' => 'Sam Seaborn',
            'email' => 'sam@whitehouse.gov',
            'data' => [
                'role' => 'Deputy Communications Director',
            ],
        ]);

        $all = Customer::all();

        $this->assertTrue($all instanceof Collection);
        $this->assertSame($all->count(), 2);
    }

    /** @test */
    public function can_find_customer()
    {
        $customer = CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
        ]);

        $find = Customer::find($customer->id);

        $this->assertSame($find->id(), $customer->id);
        $this->assertSame($find->name(), $customer->name);
        $this->assertSame($find->email(), $customer->email);
        $this->assertSame($find->get('role'), 'Press Secretary');
    }

    /** @test */
    public function can_find_customer_with_custom_column()
    {
        $customer = CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
            'favourite_colour' => 'Orange',
        ]);

        $find = Customer::find($customer->id);

        $this->assertSame($find->id(), $customer->id);
        $this->assertSame($find->name(), $customer->name);
        $this->assertSame($find->email(), $customer->email);
        $this->assertSame($find->get('role'), 'Press Secretary');
        $this->assertSame($find->get('favourite_colour'), 'Orange');
    }

    /** @test */
    public function can_find_customer_by_email()
    {
        $customer = CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
        ]);

        $find = Customer::findByEmail($customer->email);

        $this->assertSame($find->id(), $customer->id);
        $this->assertSame($find->name(), $customer->name);
        $this->assertSame($find->email(), $customer->email);
        $this->assertSame($find->get('role'), 'Press Secretary');
    }

    /** @test */
    public function can_create()
    {
        $create = Customer::make()
            ->email('sam@whitehouse.gov')
            ->data([
                'name' => 'Sam Seaborne',
            ]);

        $create->save();

        $this->assertNotNull($create->id());
        $this->assertSame($create->name(), 'Sam Seaborne');
        $this->assertSame($create->email(), 'sam@whitehouse.gov');
    }

    /** @test */
    public function can_save()
    {
        $customerRecord = CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
        ]);

        $customer = Customer::find($customerRecord->id);

        $customer->set('is_senior_advisor', true);

        $customer->save();

        $this->assertSame($customer->id(), $customerRecord->id);
        $this->assertSame($customer->get('is_senior_advisor'), true);
    }

    /** @test */
    public function can_save_with_custom_column()
    {
        $customerRecord = CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
        ]);

        $customer = Customer::find($customerRecord->id);

        $customer->set('favourite_colour', 'Yellow');

        $customer->save();

        $this->assertSame($customer->id(), $customerRecord->id);
        $this->assertSame($customer->get('favourite_colour'), 'Yellow');
    }

    /** @test */
    public function can_delete()
    {
        $customerRecord = CustomerModel::create([
            'name' => 'CJ Cregg',
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'role' => 'Press Secretary',
            ],
        ]);

        $customer = Customer::find($customerRecord->id);

        $customer->delete();

        $this->assertDatabaseMissing('customers', [
            'id' => $customerRecord->id,
            'name' => 'CJ Cregg',
            'email' => $customerRecord->email,
        ]);
    }
}
