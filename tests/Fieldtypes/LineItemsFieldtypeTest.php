<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\LineItemsFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class LineItemsFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new LineItemsFieldtype();
    }

    /** @test */
    public function it_can_return_preload_data()
    {
        $this->markTestIncomplete();
    }

    /** @test */
    public function it_can_preProcess_data()
    {
        $preProcess = $this->fieldtype->preProcess([]);

        $this->assertIsArray($preProcess);
    }

    /** @test */
    public function it_can_process_data()
    {
        $process = $this->fieldtype->process([]);

        $this->assertIsArray($process);
    }

    /** @test */
    public function it_can_return_title()
    {
        $title = $this->fieldtype->title();

        $this->assertSame($title, 'Line Items');
    }

    /** @test */
    public function it_can_return_component()
    {
        $component = $this->fieldtype->component();

        $this->assertSame($component, 'line-items');
    }
}