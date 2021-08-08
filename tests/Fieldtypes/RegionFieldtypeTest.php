<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\RegionFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RegionFieldtypeTest extends TestCase
{
    protected $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new RegionFieldtype;
    }

    /** @test */
    public function can_get_index_items()
    {
        $getIndexItems = $this->fieldtype->getIndexItems(new Request());

        $this->assertTrue($getIndexItems instanceof Collection);

        $this->assertSame($getIndexItems->last(), [
            'id' => 'zw-mw',
            'country_iso' => 'ZW',
            'country_name' => 'Zimbabwe',
            'name' => 'Mashonaland West',
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
        $toItemArray = $this->fieldtype->toItemArray('gb-sct');

        $this->assertIsArray($toItemArray);

        $this->assertSame($toItemArray, [
            'id' => 'gb-sct',
            'title' => 'Scotland',
        ]);
    }

    /** @test */
    public function can_preprocess_index()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex('gb-sct');

        $this->assertIsString($preProcessIndex);
        $this->assertSame($preProcessIndex, 'Scotland');
    }

    /** @test */
    public function can_preprocess_index_with_no_region()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(null);

        $this->assertNull($preProcessIndex);
    }

    /** @test */
    public function can_preprocess_with_multiple_regions()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(['gb-sct', 'gb-wls']);

        $this->assertIsString($preProcessIndex);
        $this->assertSame($preProcessIndex, 'Scotland, Wales');
    }
}
