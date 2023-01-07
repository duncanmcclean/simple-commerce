<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ShippingMethodFieldtype;
use DoubleThreeDigital\SimpleCommerce\Shipping\FreeShipping;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

class ShippingMethodFieldtypeTest extends TestCase
{
    protected $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new ShippingMethodFieldtype;
    }

    /** @test */
    public function can_get_config_field_items()
    {
        $configFieldItems = (new Invader($this->fieldtype))->configFieldItems();

        $this->assertIsArray($configFieldItems);
    }

    /** @test */
    public function can_get_index_items()
    {
        $getIndexItems = $this->fieldtype->getIndexItems(new Request());

        $this->assertTrue($getIndexItems instanceof Collection);

        $this->assertSame($getIndexItems->last(), [
            'id' => FreeShipping::class,
            'name' => 'Standard Post',
            'title' => 'Standard Post',
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
    }

    /** @test */
    public function can_return_as_item_array()
    {
        $toItemArray = $this->fieldtype->toItemArray(FreeShipping::class);

        $this->assertIsArray($toItemArray);

        $this->assertSame($toItemArray, [
            'id' => FreeShipping::class,
            'title' => 'Standard Post',
        ]);
    }

    /** @test */
    public function can_preprocess_index()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(FreeShipping::class);

        $this->assertIsString($preProcessIndex);
        $this->assertSame($preProcessIndex, 'Standard Post');
    }

    /** @test */
    public function can_preprocess_index_with_no_shipping_method()
    {
        $preProcessIndex = $this->fieldtype->preProcessIndex(null);

        $this->assertNull($preProcessIndex);
    }
}
