<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ProductFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new ProductFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $product = factory(Product::class)->create();

        $item = $this->fieldtype->toItemArray($product->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id' => $product['id'],
            'title' => $product['title'],
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $products = factory(Product::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id' => $products[0]['id'],
            'title' => $products[0]['title'],
        ]);
        $this->assertSame($index[1], [
            'id' => $products[1]['id'],
            'title' => $products[1]['title'],
        ]);
        $this->assertSame($index[2], [
            'id' => $products[2]['id'],
            'title' => $products[2]['title'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Product');
    }
}