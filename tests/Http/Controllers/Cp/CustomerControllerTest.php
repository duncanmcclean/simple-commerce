<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Models\Customer;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_get_customer_index()
    {
        // TODO: issues to do with the way the control panel is being loaded in tests.

//        $customers = factory(Customer::class, 5)->create();
//
//        $user = $this->actAsAdmin();
//        $response = $this->actingAs($user)->get('/cp/customers');
//
//        dd($response);
//
//        $response
//            ->assertOk()
//            ->assertSee($customers[0]->name)
//            ->assertSee($customers[0]->email)
//            ->assertSee($customers[1]->name)
//            ->assertSee($customers[1]->email)
//            ->assertSee($customers[2]->name)
//            ->assertSee($customers[2]->email)
//            ->assertSee($customers[3]->name)
//            ->assertSee($customers[3]->email)
//            ->assertSee($customers[4]->name)
//            ->assertSee($customers[4]->email);
    }
}
