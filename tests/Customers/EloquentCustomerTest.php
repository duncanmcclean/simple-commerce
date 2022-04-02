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
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'name' => 'CJ Cregg',
            ],
        ]);

        CustomerModel::create([
            'email' => 'sam@whitehouse.gov',
            'data' => [
                'name' => 'Sam Seaborne',
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
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'name' => 'CJ Cregg',
            ],
        ]);

        $find = Customer::find($customer->id);

        $this->assertSame($find->id(), $customer->id);
        $this->assertSame($find->email(), $customer->email);
        $this->assertSame($find->name(), $customer->data['name']);
    }

    /** @test */
    public function can_find_customer_by_email()
    {
        $customer = CustomerModel::create([
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'name' => 'CJ Cregg',
            ],
        ]);

        $find = Customer::findByEmail($customer->email);

        $this->assertSame($find->id(), $customer->id);
        $this->assertSame($find->email(), $customer->email);
        $this->assertSame($find->name(), $customer->data['name']);
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
        $this->assertSame($create->email(), 'sam@whitehouse.gov');
        $this->assertSame($create->name(), 'Sam Seaborne');
    }

    /** @test */
    public function can_save()
    {
        $customerRecord = CustomerModel::create([
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'name' => 'CJ Cregg',
            ],
        ]);

        $customer = Customer::find($customerRecord->id);

        $customer->set('is_senior_advisor', true);

        $customer->save();

        $this->assertSame($customer->id(), $customerRecord->id);
        $this->assertSame($customer->get('is_senior_advisor'), true);
    }

    /** @test */
    public function can_delete()
    {
        $customerRecord = CustomerModel::create([
            'email' => 'cj@whitehouse.gov',
            'data' => [
                'name' => 'CJ Cregg',
            ],
        ]);

        $customer = Customer::find($customerRecord->id);

        $customer->delete();

        $this->assertDatabaseMissing('customers', [
            'id' => $customerRecord->id,
            'email' => $customerRecord->email,
        ]);
    }
}
