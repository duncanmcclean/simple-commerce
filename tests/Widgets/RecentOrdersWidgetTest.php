<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Widgets;

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use DoubleThreeDigital\SimpleCommerce\Widgets\RecentOrdersWidget;
use Illuminate\Support\Facades\Auth;

class RecentOrdersWidgetTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->widget = new RecentOrdersWidget();
    }

    /** @test */
    public function can_return_html_with_orders()
    {
        $this->fakeUserFacade(true);

        $orders = factory(Order::class, 3)->create();
        
        $html = $this->widget->html()->render();

        $this->assertStringContainsString('Order #'.$orders[0]['id'], $html);
        $this->assertStringContainsString('Order #'.$orders[1]['id'], $html);
        $this->assertStringContainsString('Order #'.$orders[2]['id'], $html);
    }

    /** @test */
    public function can_return_html_with_no_orders()
    {
        $this->fakeUserFacade(true);
        
        $html = $this->widget->html()->render();

        $this->assertStringContainsString("No orders exist yet or you don't have permission to access them.", $html);
    }

    /** @test */
    public function can_return_html_when_user_does_not_have_permission()
    {
        $this->fakeUserFacade();

        $orders = factory(Order::class, 3)->create();
        
        $html = $this->widget->html()->render();

        $this->assertStringNotContainsString('Order #'.$orders[0]['id'], $html);
        $this->assertStringNotContainsString('Order #'.$orders[1]['id'], $html);
        $this->assertStringNotContainsString('Order #'.$orders[2]['id'], $html);
        $this->assertStringContainsString("No orders exist yet or you don't have permission to access them.", $html);
    }
}