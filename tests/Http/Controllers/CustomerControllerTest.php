<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Entry;

class CustomerControllerTest extends TestCase
{
    use SetupCollections;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_get_customer()
    {
        $customer = Entry::make()
            ->collection('customers')
            ->slug('duncan_double_three_digital')
            ->data([
                'title' => 'Duncan McClean <duncan@doublethree.digital>',
                'name'  => 'Duncan McClean',
                'email' => 'duncan@doublethree.digital',
            ]);

        $customer->save();
        $customer->fresh();

        $response = $this->getJson(route('statamic.simple-commerce.customer.index', [
            'customer' => $customer->id(),
        ]));

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data',
            ])
            ->assertSee('Duncan McClean')
            ->assertSee('duncan@doublethree.digital');
    }

    /** @test */
    public function can_update_customer()
    {
        $customer = Entry::make()
            ->collection('customers')
            ->slug('duncan_double_three_digital')
            ->data([
                'title' => 'Duncan McClean <duncan@doublethree.digital>',
                'name'  => 'Duncan McClean',
                'email' => 'duncan@doublethree.digital',
            ]);

        $customer->save();
        $customer->fresh();

        $data = [
            'vip' => true,
        ];

        $response = $this
            ->from('/account')
            ->post(route('statamic.simple-commerce.customer.update', [
                'customer' => $customer->id(),
            ]), $data);

        $response->assertRedirect('/account');

        $customer->fresh();

        $this->assertSame($customer->data()->get('vip'), true);
    }

    /** @test */
    public function can_update_customer_and_request_json()
    {
        $customer = Entry::make()
            ->collection('customers')
            ->slug('duncan_double_three_digital')
            ->data([
                'title' => 'Duncan McClean <duncan@doublethree.digital>',
                'name'  => 'Duncan McClean',
                'email' => 'duncan@doublethree.digital',
            ]);

        $customer->save();
        $customer->fresh();

        $data = [
            'vip' => true,
        ];

        $response = $this
            ->from('/account')
            ->postJson(route('statamic.simple-commerce.customer.update', [
                'customer' => $customer->id(),
            ]), $data);

        $response->assertJsonStructure([
            'status',
            'message',
            'customer',
        ]);

        $customer->fresh();

        $this->assertSame($customer->data()->get('vip'), true);
    }
}
