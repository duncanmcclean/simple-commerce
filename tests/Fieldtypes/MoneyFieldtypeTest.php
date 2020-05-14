<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class MoneyFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        factory(Currency::class)->create();

        $this->fieldtype = new MoneyFieldtype();
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

        $this->assertSame($title, 'Money');
    }

    /** @test */
    public function it_can_return_component()
    {
        $component = $this->fieldtype->component();

        $this->assertSame($component, 'money');
    }
}