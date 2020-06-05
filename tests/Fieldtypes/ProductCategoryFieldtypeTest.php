<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ProductCategoryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class ProductCategoryFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new ProductCategoryFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $category = factory(ProductCategory::class)->create();

        $item = $this->fieldtype->toItemArray($category->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id'    => $category['id'],
            'title' => $category['title'],
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $categories = factory(ProductCategory::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id'    => $categories[0]['id'],
            'title' => $categories[0]['title'],
        ]);
        $this->assertSame($index[1], [
            'id'    => $categories[1]['id'],
            'title' => $categories[1]['title'],
        ]);
        $this->assertSame($index[2], [
            'id'    => $categories[2]['id'],
            'title' => $categories[2]['title'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Product Category');
    }
}
