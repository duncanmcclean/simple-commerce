<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CurrencyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CurrencyFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new CurrencyFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $currency = factory(Currency::class)->create();

        $item = $this->fieldtype->toItemArray($currency->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id'    => $currency['id'],
            'title' => $currency['symbol'].' '.$currency['name'],
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $currencies = factory(Currency::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id'    => $currencies[0]['id'],
            'title' => $currencies[0]['symbol'].' '.$currencies[0]['name'],
        ]);
        $this->assertSame($index[1], [
            'id'    => $currencies[1]['id'],
            'title' => $currencies[1]['symbol'].' '.$currencies[1]['name'],
        ]);
        $this->assertSame($index[2], [
            'id'    => $currencies[2]['id'],
            'title' => $currencies[2]['symbol'].' '.$currencies[2]['name'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Currency');
    }
}
