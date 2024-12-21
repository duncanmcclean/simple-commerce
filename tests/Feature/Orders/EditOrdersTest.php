<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class EditOrdersTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_edit_order()
    {
        $order = tap(Order::make()->orderNumber(1002))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->get(cp_route('simple-commerce.orders.edit', $order->id()))
            ->assertOk()
            ->assertSee('Order #1002');
    }

    #[Test]
    public function cant_edit_order_without_permissions()
    {
        $order = tap(Order::make())->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->get(cp_route('simple-commerce.orders.edit', $order->id()))
            ->assertRedirect('/cp');
    }
}
