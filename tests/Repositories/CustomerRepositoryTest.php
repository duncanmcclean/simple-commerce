<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Repositories;

use DoubleThreeDigital\SimpleCommerce\Facades\Customer;
use DoubleThreeDigital\SimpleCommerce\Repositories\CustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Entry;

class CustomerRepositoryTest extends TestCase
{
    /** @test */
    public function can_find_customer()
    {
        Entry::make()
            ->collection('customers')
            ->slug('donald-at-examplecom')
            ->data([
                'title' => 'Donald Duck <donald@example.com>',
                'name' => 'Donald Duck',
                'email' => 'donald@example.com',
            ])
            ->save();

        $entry = Entry::findBySlug('donald-at-examplecom', 'customers');

        $customer = Customer::find($entry->id());

        $this->assertSame($customer->title, 'Donald Duck <donald@example.com>');
        $this->assertSame($customer->data['name'], 'Donald Duck');
        $this->assertSame($customer->data['email'], 'donald@example.com');
    }

    /** @test */
    public function can_find_customer_with_null_title_and_slug_and_see_they_have_been_generated()
    {
        Entry::make()
            ->collection('customers')
            ->data([
                'name' => 'Mickey Mouse',
                'email' => 'mickey@example.com',
            ])
            ->save();

        $entry = Entry::whereCollection('customers')->last();

        $this->assertFalse($entry->has('title'));
        $this->assertNull($entry->slug());

        $customer = Customer::find($entry->id());

        $this->assertNotNull($customer->slug);
        $this->assertSame($customer->slug, 'mickey-at-examplecom');
        $this->assertSame($customer->title, 'Mickey Mouse <mickey@example.com>');
        $this->assertSame($customer->data['name'], 'Mickey Mouse');
        $this->assertSame($customer->data['email'], 'mickey@example.com');
    }

    /** @test */
    public function can_find_customer_by_email()
    {
        Entry::make()
            ->collection('customers')
            ->slug('minnie-at-examplecom')
            ->data([
                'title' => 'Minnie Mouse <minnie@example.com>',
                'name' => 'Minnie Mouse',
                'email' => 'minnie@example.com',
            ])
            ->save();

        $customer = Customer::findByEmail('minnie@example.com');

        $this->assertSame($customer->title, 'Minnie Mouse <minnie@example.com>');
        $this->assertSame($customer->data['name'], 'Minnie Mouse');
        $this->assertSame($customer->data['email'], 'minnie@example.com');
    }

    /** @test */
    public function can_generate_title_and_slug_from_name_and_email()
    {
        $repo = new CustomerRepository();
        $repo->data['name'] = 'Duncan McClean';
        $repo->data['email'] = 'duncan@doublethree.digital';

        $generate = $repo->generateTitleAndSlug();

        $this->assertSame($repo->title, 'Duncan McClean <duncan@doublethree.digital>');
        $this->assertSame($repo->slug, 'duncan-at-doublethreedigital');
    }

    /** @test */
    public function can_generate_title_and_slug_from_just_email()
    {
        $repo = new CustomerRepository();
        $repo->data['email'] = 'james@example.com';

        $generate = $repo->generateTitleAndSlug();

        $this->assertSame($repo->title, ' <james@example.com>');
        $this->assertSame($repo->slug, 'james-at-examplecom');
    }
}
