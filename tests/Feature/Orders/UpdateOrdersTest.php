<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class UpdateOrdersTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('products')->save();
    }

    #[Test]
    public function can_update_order()
    {
        $order = tap(Order::make()->orderNumber(1002))->save();

        $this
            ->actingAs(User::make()->makeSuper()->save())
            ->patch(cp_route('simple-commerce.orders.update', $order->id()), [
                'shipping_line_1' => '123 Fake Street',
                'shipping_city' => 'Fakeville',
                'shipping_postcode' => 'FA1 1KE',
                'shipping_country' => 'United Kingdom',
                'grand_total' => 1000, // This should be ignored.
            ])
            ->assertOk()
            ->assertSee('Order #1002');

        $order = $order->fresh();

        $this->assertEquals($order->get('shipping_line_1'), '123 Fake Street');
        $this->assertEquals($order->get('shipping_city'), 'Fakeville');
        $this->assertEquals($order->get('shipping_postcode'), 'FA1 1KE');
        $this->assertEquals($order->get('shipping_country'), 'United Kingdom');
        $this->assertEquals($order->grandTotal(), 0);
    }

    #[Test]
    public function cant_update_order_without_permissions()
    {
        $order = tap(Order::make())->save();

        Role::make('test')->addPermission('access cp')->save();

        $this
            ->actingAs(User::make()->assignRole('test')->save())
            ->patch(cp_route('simple-commerce.orders.update', $order->id()))
            ->assertRedirect('/cp');
    }
}
