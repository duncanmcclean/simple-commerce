<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\OrderStatusFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class OrderStatusFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new OrderStatusFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $status = factory(OrderStatus::class)->create();

        $item = $this->fieldtype->toItemArray($status->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id'    => $status['id'],
            'title' => $status['name'],
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $statuses = factory(OrderStatus::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id'    => $statuses[0]['id'],
            'title' => $statuses[0]['name'],
        ]);
        $this->assertSame($index[1], [
            'id'    => $statuses[1]['id'],
            'title' => $statuses[1]['name'],
        ]);
        $this->assertSame($index[2], [
            'id'    => $statuses[2]['id'],
            'title' => $statuses[2]['name'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Order Status');
    }
}
