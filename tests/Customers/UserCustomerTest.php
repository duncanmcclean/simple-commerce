<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Customers;

use DoubleThreeDigital\SimpleCommerce\Customers\UserCustomer;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Facades\User;

class UserCustomerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('simple-commerce.content.customers', [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomer::class,
        ]);

        $this->app->bind(\DoubleThreeDigital\SimpleCommerce\Contracts\Customer::class, \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomer::class);

        File::deleteDirectory(__DIR__.'/../__fixtures__/users');

        app('stache')->stores()->get('users')->clear();
    }

    public function tearDown(): void
    {
        parent::tearDownAfterClass();

        $this->app['config']->set('simple-commerce.content.customers', [
            'driver' => \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class,
        ]);

        $this->app->bind(\DoubleThreeDigital\SimpleCommerce\Contracts\Customer::class, \DoubleThreeDigital\SimpleCommerce\Customers\Customer::class);
    }

    /** @test */
    public function can_get_all_users()
    {
        User::make()
            ->email('james@example.com')
            ->save();

        User::make()
            ->email('ben@example.com')
            ->save();

        $all = Customer::all();

        $this->assertTrue($all instanceof Collection);
        $this->assertSame($all->count(), 2);
    }

    /** @test */
    public function can_query_users()
    {
        // TODO
        $this->markTestIncomplete("No one really uses `query` so I'm going to leave this out for now.");
    }

    /** @test */
    public function can_find_user()
    {
        $user = User::make()->email('james@example.com')->set('name', 'James Example');
        $user->save();

        $find = Customer::find($user->id());

        $this->assertTrue($find instanceof UserCustomer);

        $this->assertSame($find->id(), $user->id());
        $this->assertSame($find->name(), $user->get('name'));
        $this->assertSame($find->email(), $user->email());
    }

    /** @test */
    public function can_find_user_by_email()
    {
        $user = User::make()->email('ben@example.com')->set('name', 'Ben Example');
        $user->save();

        $findByEmail = Customer::findByEmail($user->email());

        $this->assertTrue($findByEmail instanceof UserCustomer);

        $this->assertSame($findByEmail->id(), $user->id());
        $this->assertSame($findByEmail->name(), $user->get('name'));
        $this->assertSame($findByEmail->email(), $user->email());
    }
}
