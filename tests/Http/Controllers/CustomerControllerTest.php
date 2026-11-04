<?php

use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Tests\Fixtures\Http\Requests\CustomerUpdateFormRequest;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\Config;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

uses(SetupCollections::class);

test('can get customer on cart', function () {
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

    $response = $this
        ->withSession(['simple-commerce-cart' => cartBelongingTo($customer)->id()])
        ->getJson(route('statamic.simple-commerce.customer.index', [
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

test('can get customer as logged in user', function () {
    $customer = Entry::make()
        ->collection('customers')
        ->slug('duncan_double_three_digital')
        ->data([
            'title' => 'Duncan McClean <duncan@doublethree.digital>',
            'name' => 'Duncan McClean',
            'email' => 'duncan@doublethree.digital',
        ]);

    $customer->save();

    $user = User::make()->email('duncan@doublethree.digital');
    $user->save();

    $this
        ->actingAs($user)
        ->getJson(route('statamic.simple-commerce.customer.index', [
            'customer' => $customer->id(),
        ]))
        ->assertOk()
        ->assertSee('duncan@doublethree.digital');
});

test('cant get customer belonging to someone else', function () {
    $customer = Entry::make()
        ->collection('customers')
        ->slug('duncan_double_three_digital')
        ->data([
            'title' => 'Duncan McClean <duncan@doublethree.digital>',
            'name' => 'Duncan McClean',
            'email' => 'duncan@doublethree.digital',
        ]);

    $customer->save();

    $user = User::make()->email('joe.bloggs@example.com');
    $user->save();

    $this
        ->actingAs($user)
        ->withSession(['simple-commerce-cart' => Order::make()->save()->id()])
        ->getJson(route('statamic.simple-commerce.customer.index', [
            'customer' => $customer->id(),
        ]))
        ->assertForbidden()
        ->assertDontSee('duncan@doublethree.digital');
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
        ->withSession(['simple-commerce-cart' => cartBelongingTo($customer)->id()])
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
        ->withSession(['simple-commerce-cart' => cartBelongingTo($customer)->id()])
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

test('cant update customer belonging to someone else', function () {
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

    $this
        ->withSession(['simple-commerce-cart' => Order::make()->save()->id()])
        ->postJson(route('statamic.simple-commerce.customer.update', [
            'customer' => $customer->id(),
        ]), ['email' => 'attacker@example.com', 'vip' => true])
        ->assertForbidden();

    expect($customer->fresh()->get('email'))->toBe('duncan@doublethree.digital');
    expect($customer->fresh()->get('vip'))->toBeNull();
});

test('cant update customer when email contains path characters', function (string $email) {
    Config::set('simple-commerce.field_whitelist.customers', [
        'name', 'email',
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

    $this
        ->withSession(['simple-commerce-cart' => cartBelongingTo($customer)->id()])
        ->from('/account')
        ->post(route('statamic.simple-commerce.customer.update', [
            'customer' => $customer->id(),
        ]), ['email' => $email])
        ->assertSessionHasErrors('email');

    expect($customer->fresh()->get('email'))->toBe('duncan@doublethree.digital');
})->with([
    'leading slash' => '/joe@example.com',
    'quoted traversal' => '"x/../../p"@example.com',
    'backslash' => '"x\\y"@example.com',
]);

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
        ->withSession(['simple-commerce-cart' => cartBelongingTo($customer)->id()])
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
function cartBelongingTo($customer)
{
    return Order::make()->customer($customer->id())->save();
}

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
