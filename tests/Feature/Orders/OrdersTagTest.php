<?php

namespace Tests\Feature\Orders;

use DuncanMcClean\SimpleCommerce\Facades\Order;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Parse;
use Statamic\Query\Scopes\Scope;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use Tests\TestCase;

class OrdersTagTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_returns_orders()
    {
        Order::make()->orderNumber(1000)->grandTotal(1000)->save();
        Order::make()->orderNumber(1001)->grandTotal(1234)->save();
        Order::make()->orderNumber(1002)->grandTotal(2479)->save();

        $output = $this->tag('{{ orders }}{{ order_number }}-{{ grand_total }}{{ /orders }}');

        $this->assertStringContainsString('1000-£10.00', $output);
        $this->assertStringContainsString('1001-£12.34', $output);
        $this->assertStringContainsString('1002-£24.79', $output);
    }

    #[Test]
    public function it_filters_orders_by_site()
    {
        $this->setSites([
            'en' => ['name' => 'English', 'locale' => 'en_US', 'url' => 'http://test.com/'],
            'de' => ['name' => 'German', 'locale' => 'de_DE', 'url' => 'http://test.com/de/'],
        ]);

        Order::make()->orderNumber(1000)->site('en')->save();
        Order::make()->orderNumber(1001)->site('de')->save();
        Order::make()->orderNumber(1002)->site('de')->save();

        $output = $this->tag('{{ orders site="de" }}{{ order_number }}{{ /orders }}');

        $this->assertStringNotContainsString('1000', $output);
        $this->assertStringContainsString('1001', $output);
        $this->assertStringContainsString('1002', $output);
    }

    #[Test]
    public function it_filters_orders_using_conditions()
    {
        Order::make()->orderNumber(1000)->grandTotal(1000)->save();
        Order::make()->orderNumber(1001)->grandTotal(1234)->save();
        Order::make()->orderNumber(1002)->grandTotal(2479)->save();

        $output = $this->tag('{{ orders order_number:gte="1001" }}{{ order_number }}-{{ grand_total }}{{ /orders }}');

        $this->assertStringNotContainsString('1000-£10.00', $output);
        $this->assertStringContainsString('1001-£12.34', $output);
        $this->assertStringContainsString('1002-£24.79', $output);
    }

    #[Test]
    public function it_sorts_orders()
    {
        Order::make()->orderNumber(1000)->grandTotal(1000)->save();
        Order::make()->orderNumber(1001)->grandTotal(1234)->save();
        Order::make()->orderNumber(1002)->grandTotal(2479)->save();

        $output = $this->tag('{{ orders sort="order_number:desc" }}{{ order_number }}-{{ /orders }}');

        $this->assertEquals('1002-1001-1000-', $output);
    }

    #[Test]
    public function it_uses_query_scope()
    {
        Order::make()->orderNumber(1000)->grandTotal(1000)->save();
        Order::make()->orderNumber(1001)->grandTotal(1234)->save();
        Order::make()->orderNumber(1002)->grandTotal(2479)->save();

        DummyQueryScope::register();

        $output = $this->tag('{{ orders query_scope="dummy_query_scope" }}{{ order_number }}{{ /orders }}');

        $this->assertStringNotContainsString('1000', $output);
        $this->assertStringNotContainsString('1001', $output);
        $this->assertStringContainsString('1002', $output);
    }

    #[Test]
    public function it_paginates_results()
    {
        Order::make()->orderNumber(1000)->grandTotal(1000)->save();
        Order::make()->orderNumber(1001)->grandTotal(1234)->save();
        Order::make()->orderNumber(1002)->grandTotal(2479)->save();

        $output = $this->tag('{{ orders paginate="1" as="items" }}{{ items }}{{ order_number }}{{ /items }} {{ paginate:total_pages }} pages{{ /orders }}');

        $this->assertStringContainsString('1000', $output);
        $this->assertStringNotContainsString('1001', $output);
        $this->assertStringNotContainsString('1002', $output);
        $this->assertStringContainsString('3 pages', $output);
    }

    private function tag($tag, $data = [])
    {
        return (string) Parse::template($tag, $data);
    }
}

class DummyQueryScope extends Scope
{
    public function apply($query, $values)
    {
        $query->where('grand_total', '>=', 2000);
    }
}