<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Customers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Customer as CustomerContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as ContractsOrder;
use DoubleThreeDigital\SimpleCommerce\Customers\UserCustomer;
use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Statamic\Contracts\Auth\User as AuthUser;
use Statamic\Facades\User;
use Statamic\Http\Resources\API\UserResource;
use Statamic\Statamic;

class UserCustomerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Statamic::repository(
            \DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository::class,
            \DoubleThreeDigital\SimpleCommerce\Customers\UserCustomerRepository::class
        );

        File::deleteDirectory(__DIR__ . '/../__fixtures__/users');

        app('stache')->stores()->get('users')->clear();
    }

    public function tearDown(): void
    {
        parent::tearDownAfterClass();

        Statamic::repository(
            \DoubleThreeDigital\SimpleCommerce\Contracts\CustomerRepository::class,
            \DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository::class
        );
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
        User::make()
            ->email('james@example.com')
            ->save();

        User::make()
            ->email('ben@example.com')
            ->save();

        $query = Customer::all();

        $this->assertTrue($query instanceof Collection);
        $this->assertSame($query->count(), 2);
    }

    /** @test */
    public function can_find_user()
    {
        $user = User::make()->email('james@example.com')->set('name', 'James Example');
        $user->save();

        $find = Customer::find($user->id());

        // $this->assertTrue($find instanceof UserCustomer);

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

        // $this->assertTrue($findByEmail instanceof UserCustomer);

        $this->assertSame($findByEmail->id(), $user->id());
        $this->assertSame($findByEmail->name(), $user->get('name'));
        $this->assertSame($findByEmail->email(), $user->email());
    }

    /** @test */
    public function can_create()
    {
        $create = Customer::make()
            ->email('joe.smith@example.com')
            ->data([
                'name' => 'Joe Smith',
            ]);

        $create->save();

        // $this->assertTrue($create instanceof UserCustomer);

        $this->assertNotNull($create->id());
        $this->assertSame($create->name(), 'Joe Smith');
        $this->assertSame($create->email(), 'joe.smith@example.com');
    }

    /** @test */
    public function can_save()
    {
        $user = User::make()->id('sarah')->email('sarah@example.com')->set('name', 'Sarah Example');
        $user->save();

        $customer = Customer::find('sarah');
        $customer->name = 'Sarah Test';

        $customer->set('name', 'Sarah Test');

        $customer->save();

        $this->assertSame($user->id(), 'sarah');
        $this->assertSame($customer->name(), 'Sarah Test');
    }

    /** @test */
    public function can_delete()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $customer->delete();

        $this->assertNull(User::find('sam'));
    }

    /** @test */
    public function can_get_user()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        // $this->assertTrue($customer instanceof UserCustomer);
        $this->assertTrue($customer->resource() instanceof AuthUser); // TODO
    }

    /** @test */
    public function can_get_customer_to_resource()
    {
        $this->markTestSkipped();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');
        $toResource = $customer->toResource();

        $this->assertTrue($toResource instanceof UserResource);
    }

    /** @test */
    public function can_get_customer_to_augmented_array()
    {
        $this->markTestSkipped();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');
        $toAugmentedArray = $customer->toAugmentedArray();

        $this->assertIsArray($toAugmentedArray);
    }

    /** @test */
    public function can_get_customer_to_array()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');
        $toArray = $customer->toArray();

        $this->assertIsArray($toArray);
    }

    /** @test */
    public function can_get_id()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->id(), 'sam');
    }

    /** @test */
    public function can_get_title()
    {
        $this->markTestSkipped();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->title(), 'Sam Example <sam@example.com>');
    }

    /** @test */
    public function can_get_slug()
    {
        $this->markTestSkipped();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->slug(), 'sam');
    }

    /** @test */
    public function can_get_site()
    {
        $this->markTestSkipped();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertNull($customer->site());
    }

    /** @test */
    public function can_get_fresh()
    {
        $this->markTestSkipped();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->name(), 'Sam Example');

        $user->set('name', 'Sam Test')->save();

        $fresh = $customer->fresh();

        $this->assertNotSame($customer->name(), 'Sam Example');
        $this->assertSame($customer->name(), 'Sam Test');
    }

    /** @test */
    public function can_get_name()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->name(), 'Sam Example');
    }

    /** @test */
    public function can_get_email()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->email(), 'sam@example.com');
    }

    /** @test */
    public function can_get_orders()
    {
        $order = Order::make()->merge(['title' => 'Order #0001']);
        $order->save();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example')->set('orders', [$order->id()]);
        $user->save();

        $customer = Customer::find('sam');

        $this->assertTrue($customer->orders() instanceof Collection);
        $this->assertTrue($customer->orders()->first() instanceof ContractsOrder);

        $this->assertSame($customer->orders()->first()->get('title'), 'Order #0001');
    }

    /** @test */
    public function can_add_order()
    {
        $order = Order::make()->merge(['title' => 'Order #0002']);
        $order->save();

        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');
        $customer->addOrder($order->id())->save();

        $this->assertSame($customer->orders()->count(), 1);
        $this->assertSame($customer->orders()->first()->get('title'), 'Order #0002');
    }

    /** @test */
    public function can_get_mail_notification_route()
    {
        $user = User::make()->id('sam')->email('sam@example.com')->set('name', 'Sam Example');
        $user->save();

        $customer = Customer::find('sam');

        $this->assertSame($customer->routeNotificationForMail(), 'sam@example.com');
    }

    /** @test */
    public function can_get_blueprint_default_fields()
    {
        $this->markTestSkipped("The `defaultFieldsInBlueprint` method doesn't seem to exist here.");

        $customerInstance = resolve(CustomerContract::class);

        $defaultFieldsInBlueprint = (new Invader($customerInstance))->defaultFieldsInBlueprint();

        $this->assertIsArray($defaultFieldsInBlueprint);
    }
}
