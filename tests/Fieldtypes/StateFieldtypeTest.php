<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\StateFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\State;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class StateFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new StateFieldtype();
    }

    /** @test */
    public function it_can_get_item_array()
    {
        $state = factory(State::class)->create();

        $item = $this->fieldtype->toItemArray($state->id);

        $this->assertIsArray($item);
        $this->assertSame($item, [
            'id' => $state['id'],
            'title' => $state['name'],
        ]);
    }

    /** @test */
    public function it_can_get_item_index()
    {
        $states = factory(State::class, 3)->create();

        $index = $this->fieldtype->getIndexItems([]);

        $this->assertSame($index[0], [
            'id' => $states[0]['id'],
            'title' => $states[0]['name'],
        ]);
        $this->assertSame($index[1], [
            'id' => $states[1]['id'],
            'title' => $states[1]['name'],
        ]);
        $this->assertSame($index[2], [
            'id' => $states[2]['id'],
            'title' => $states[2]['name'],
        ]);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'State');
    }
}