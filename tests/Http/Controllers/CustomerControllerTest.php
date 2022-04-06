<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers;

use DoubleThreeDigital\SimpleCommerce\Tests\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Http\FormRequest;
use Statamic\Facades\Entry;

class CustomerControllerTest extends TestCase
{
    use SetupCollections;

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

    /** @test */
    public function can_update_customer_and_ensure_custom_form_request_is_used()
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
            '_request' => encrypt(CustomerUpdateFormRequest::class),
            'vip' => true,
        ];

        $response = $this
            ->from('/account')
            ->post(route('statamic.simple-commerce.customer.update', [
                'customer' => $customer->id(),
            ]), $data)
            ->assertSessionHasErrors('business_name');

        $this->assertEquals(session('errors')->default->first('business_name'), "You can't have a business without a name. Silly sausage!");

        $response->assertRedirect('/account');

        $customer->fresh();

        $this->assertArrayNotHasKey('vip', $customer->data());
    }
}

class CustomerUpdateFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'business_name' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'business_name.required' => "You can't have a business without a name. Silly sausage!",
        ];
    }
}
