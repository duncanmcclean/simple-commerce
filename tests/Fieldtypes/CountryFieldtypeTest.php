<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Support\Countries;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CountryFieldtypeTest extends TestCase
{
    protected $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new CountryFieldtype;
    }

    /** @test */
    public function can_get_index_items()
    {
        $getIndexItems = $this->fieldtype->getIndexItems(new Request());

        $this->assertTrue($getIndexItems instanceof Collection);

        $this->assertSame($getIndexItems->last(), [
            'id' => 'ZW',
            'iso' => 'ZW',
            'name' => 'Zimbabwe',
        ]);
    }

    /** @test */
    public function can_get_columns()
    {
        $this->markTestIncomplete('Can not test this method as it is protected.');
    }

    /** @test */
    public function can_return_as_item_array()
    {
        $toItemArray = $this->fieldtype->toItemArray('GB');

        $this->assertIsArray($toItemArray);

        $this->assertSame($toItemArray, [
            'id' => 'GB',
            'title' => 'United Kingdom',
        ]);
    }

    /** @test */
    public function can_preprocess_index()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex('GB');

        $this->assertIsString($preProcessIndex);
        $this->assertSame($preProcessIndex, 'United Kingdom');
    }

    /** @test */
    public function can_preprocess_index_with_no_country()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(null);

        $this->assertNull($preProcessIndex);
    }

    /** @test */
    public function can_preprocess_with_multiple_countries()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(['GB', 'US']);

        $this->assertIsString($preProcessIndex);
        $this->assertSame($preProcessIndex, 'United Kingdom, United States');
    }
}
