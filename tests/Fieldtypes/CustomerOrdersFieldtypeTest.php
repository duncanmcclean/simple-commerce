<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CustomerOrdersFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CustomerOrdersFieldtypeTest extends TestCase
{
    public $fieldtype;

    public function setUp(): void
    {
        parent::setUp();

        $this->fieldtype = new CustomerOrdersFieldtype();
    }

    /** @test */
    public function it_can_return_preload_data()
    {
        $preload = $this->fieldtype->preload();

        $this->assertSame($preload, cp_route('fieldtype-data.customer-orders'));
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

        $this->assertSame($title, 'Customer Orders');
    }

    /** @test */
    public function it_can_return_component()
    {
        $component = $this->fieldtype->component();

        $this->assertSame($component, 'customer-orders');
    }
}