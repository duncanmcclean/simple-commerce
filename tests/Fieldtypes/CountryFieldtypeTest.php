<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

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
        $getColumns = (new Invader($this->fieldtype))->getColumns();

        $this->assertIsArray($getColumns);

        $this->assertTrue($getColumns[0] instanceof Column);
        $this->assertSame($getColumns[0]->field(), 'name');
        $this->assertSame($getColumns[0]->label(), 'Name');

        $this->assertTrue($getColumns[1] instanceof Column);
        $this->assertSame($getColumns[1]->field(), 'iso');
        $this->assertSame($getColumns[1]->label(), 'ISO Code');
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
