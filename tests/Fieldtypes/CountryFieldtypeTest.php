<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtype;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\Country;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CountryFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new CountryFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $country = factory(Country::class)->create();

        $item = $this->fieldtype->toItemArray($country->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id' => $country->id,
            'title' => $country->name,
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $countries = factory(Country::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id' => $countries[0]['id'],
            'title' => $countries[0]['name'],
        ]);
        $this->assertSame($index[1], [
            'id' => $countries[1]['id'],
            'title' => $countries[1]['name'],
        ]);
        $this->assertSame($index[2], [
            'id' => $countries[2]['id'],
            'title' => $countries[2]['name'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Country');
    }
}