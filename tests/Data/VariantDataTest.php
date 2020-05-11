<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Data;

use DoubleThreeDigital\SimpleCommerce\Data\ProductData;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class VariantDataTest extends TestCase
{
    /** @test */
    public function it_can_do_all_the_things()
    {
        $currency = factory(Currency::class)->create();

        $variant = factory(Variant::class)->create();
        $attribute = factory(Attribute::class)->create(['key' => 'foo', 'value' => 'bar', 'attributable_type' => Variant::class, 'attributable_id' => $variant->id]);

        $data = (new ProductData)->data($variant->toArray(), $variant);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('images', $data);
        $this->assertArrayHasKey('price', $data);
        $this->assertArrayHasKey('foo', $data);
    }
}