<?php

namespace Tests\Feature\Customers;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class GuestCustomerTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_can_convert_a_guest_customer_to_a_user(): void
    {
        $orderA = tap(Order::make()->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@example.com']))->save();
        $orderB = tap(Order::make()->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@example.com']))->save();
        $orderC = tap(Order::make()->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@example.com']))->save();

        $this->assertNull(User::find('cj.cregg@example.com'));

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.fieldtypes.convert-guest-customer'), [
                'email' => 'cj.cregg@example.com',
                'order_id' => $orderA->id(),
            ])
            ->assertOk();

        $this->assertNotNull($user = User::findByEmail('cj.cregg@example.com'));

        $this->assertEquals($user, $orderA->fresh()->customer());
        $this->assertEquals($user, $orderB->fresh()->customer());
        $this->assertEquals($user, $orderC->fresh()->customer());
    }

    #[Test]
    public function it_can_convert_a_guest_customer_to_a_user_when_user_already_exists(): void
    {
        $order = tap(Order::make()->customer(['name' => 'CJ Cregg', 'email' => 'cj.cregg@example.com']))->save();

        $user = User::make()->email('cj.cregg@example.com')->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->post(cp_route('simple-commerce.fieldtypes.convert-guest-customer'), [
                'email' => 'cj.cregg@example.com',
                'order_id' => $order->id(),
            ])
            ->assertOk();

        $this->assertEquals($user->id(), $order->fresh()->customer()->id());
    }
}
