<?php

use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CustomerUpdateFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Entry;

uses(SetupCollections::class);

test('can get customer', function () {
    $customer = Entry::make()
        ->collection('customers')
        ->slug('duncan_double_three_digital')
        ->data([
            'title' => 'Duncan McClean <duncan@doublethree.digital>',
            'name' => 'Duncan McClean',
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
});

test('can update customer', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'vip',
    ]);

    $customer = Entry::make()
        ->collection('customers')
        ->slug('duncan_double_three_digital')
        ->data([
            'title' => 'Duncan McClean <duncan@doublethree.digital>',
            'name' => 'Duncan McClean',
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

    expect(true)->toBe($customer->data()->get('vip'));
});

test('can update customer and request json', function () {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email', 'vip',
    ]);

    $customer = Entry::make()
        ->collection('customers')
        ->slug('duncan_double_three_digital')
        ->data([
            'title' => 'Duncan McClean <duncan@doublethree.digital>',
            'name' => 'Duncan McClean',
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

    expect(true)->toBe($customer->data()->get('vip'));
});

test('can update customer and ensure custom form request is used', function () {
    $customer = Entry::make()
        ->collection('customers')
        ->slug('duncan_double_three_digital')
        ->data([
            'title' => 'Duncan McClean <duncan@doublethree.digital>',
            'name' => 'Duncan McClean',
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

    expect("You can't have a business without a name. Silly sausage!")->toEqual(session('errors')->default->first('business_name'));

    $response->assertRedirect('/account');

    $customer->fresh();

    $this->assertArrayNotHasKey('vip', $customer->data());
});

// Helpers
function authorize()
{
    return true;
}

function rules()
{
    return [
        'business_name' => ['required', 'string'],
    ];
}

function messages()
{
    return [
        'business_name.required' => "You can't have a business without a name. Silly sausage!",
    ];
}
