<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\RegionFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

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
        $getColumns = (new Invader($this->fieldtype))->getColumns();

        $this->assertIsArray($getColumns);

        $this->assertTrue($getColumns[0] instanceof Column);
        $this->assertSame($getColumns[0]->field(), 'name');
        $this->assertSame($getColumns[0]->label(), 'Name');

        $this->assertTrue($getColumns[1] instanceof Column);
        $this->assertSame($getColumns[1]->field(), 'country_name');
        $this->assertSame($getColumns[1]->label(), 'Country');
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
