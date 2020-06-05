<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Data;

use DoubleThreeDigital\SimpleCommerce\Data\LineItemData;
use DoubleThreeDigital\SimpleCommerce\Models\LineItem;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class LineItemDataTest extends TestCase
{
    /** @test */
    public function it_can_do_all_the_things()
    {
        $lineItem = factory(LineItem::class)->create();

        $data = (new LineItemData())->data($lineItem->toArray(), $lineItem);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('sku', $data);
        $this->assertArrayHasKey('variant', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('total', $data);
    }
}
