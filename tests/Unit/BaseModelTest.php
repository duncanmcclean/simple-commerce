<?php

namespace Damcclean\Commerce\Tests\Unit;

use Damcclean\Commerce\Models\BaseModel;
use Damcclean\Commerce\Tests\TestCase as TestCase;

class BaseModelTest extends TestCase
{
    public function setUp() : void
    {
        parent::setUp();

        $this->base = new BaseModel(__DIR__.'/../stubs/content/commerce/product');
        $this->base->name = 'Product';
        $this->base->slug = 'product';
        $this->base->route = 'products';
    }

    /** @test */
    public function can_get_attributes()
    {
        $attributes = $this->base->attributes(__DIR__.'/../stubs/content/commerce/product/star-wars.md');

        $this->assertIsArray($attributes);
        $this->assertSame('Star Wars', $attributes['title']);
    }

    /** @test */
    public function can_get_all_items()
    {
        //
    }

    /** @test */
    public function can_get_item()
    {

    }

    /** @test */
    public function can_search_items()
    {
        //
    }

    /** @test */
    public function can_save_item()
    {
        //
    }

    /** @test */
    public function can_update_item()
    {
        //
    }

    /** @test */
    public function can_delete_item()
    {
        //
    }

    /** @test */
    public function can_get_edit_route()
    {
        //
    }
}
