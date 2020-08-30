<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Tests\CollectionSetup;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Entry;

class CustomerControllerTest extends TestCase
{
    use CollectionSetup;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupCollections();
    }

    /** @test */
    public function can_get_customer()
    {
        Entry::make()
            ->collection('customers')
            ->slug('duncan_double_three_digital')
            ->data([
                'title' => 'Duncan McClean <duncan@doublethree.digital>',
                'name' => 'Duncan McClean',
                'email' => 'duncan@doublethree.digital',
            ])
            ->save();

        $customer = Entry::findBySlug('duncan_double_three_digital', 'customers');

        $response = $this->get(route('statamic.simple-commerce.customer.index', [
            'customer' => $customer->id(),
        ]));

        $response->assertOk();
        $response->assertSee('Duncan McClean');
        $response->assertSee('duncan@doublethree.digital');
    }

    /** @test */
    public function can_update_customer()
    {
        Entry::make()
            ->collection('customers')
            ->slug('duncan_double_three_digital')
            ->data([
                'title' => 'Duncan McClean <duncan@doublethree.digital>',
                'name' => 'Duncan McClean',
                'email' => 'duncan@doublethree.digital',
            ])
            ->save();

        $customer = Entry::findBySlug('duncan_double_three_digital', 'customers');

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
}
