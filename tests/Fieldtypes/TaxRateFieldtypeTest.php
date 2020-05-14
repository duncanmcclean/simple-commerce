<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\TaxRateFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class TaxRateFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new TaxRateFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $rate = factory(TaxRate::class)->create();

        $item = $this->fieldtype->toItemArray($rate->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id' => $rate['id'],
            'title' => $rate['name'],
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $rates = factory(TaxRate::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id' => $rates[0]['id'],
            'title' => $rates[0]['name'],
        ]);
        $this->assertSame($index[1], [
            'id' => $rates[1]['id'],
            'title' => $rates[1]['name'],
        ]);
        $this->assertSame($index[2], [
            'id' => $rates[2]['id'],
            'title' => $rates[2]['name'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Tax Rate');
    }
}