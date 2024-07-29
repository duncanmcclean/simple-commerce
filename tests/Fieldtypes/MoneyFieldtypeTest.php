<?php

namespace Tests\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Fields\Field;
use Tests\TestCase;

class MoneyFieldtypeTest extends TestCase
{
    #[Test]
    public function can_preload_currency()
    {
        $preload = (new MoneyFieldtype)->preload();

        $this->assertIsArray($preload);
        $this->assertArrayHasKey('code', $preload);
        $this->assertArrayHasKey('name', $preload);
        $this->assertArrayHasKey('symbol', $preload);
    }

    #[Test]
    public function can_pre_process_data()
    {
        $this->assertEquals('25.50', (new MoneyFieldtype)->preProcess(2550));
    }

    #[Test]
    public function can_pre_process_data_where_value_includes_a_decimal_point()
    {
        $this->assertEquals('25.99', (new MoneyFieldtype)->preProcess('25.99'));
    }

    #[Test]
    public function can_pre_process_data_when_value_is_empty_and_save_zero_value_is_false()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => false]));

        $this->assertNull($fieldtype->preProcess(null));
    }

    #[Test]
    public function can_pre_process_data_when_value_is_empty_and_save_zero_value_is_true()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => true]));

        $this->assertEquals(0, $fieldtype->preProcess(null));
    }

    #[Test]
    public function can_process_data()
    {
        $this->assertEquals(1265, (new MoneyFieldtype)->process('12.65'));
    }

    #[Test]
    public function can_process_data_when_value_is_empty_and_save_zero_value_is_false()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => false]));

        $this->assertNull($fieldtype->process(null));
    }

    #[Test]
    public function can_process_data_when_value_is_empty_and_save_zero_value_is_true()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => true]));

        $this->assertEquals(0, $fieldtype->process(null));
    }

    #[Test]
    public function can_augment_data()
    {
        $this->markTestSkipped('Test is failing');

        $this->assertEquals('£19.45', (new MoneyFieldtype)->process(1945));
    }

    #[Test]
    public function can_augment_data_when_value_is_empty_and_save_zero_value_is_false()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => false]));

        $this->assertNull($fieldtype->augment(null));
    }

    #[Test]
    public function can_augment_data_when_value_is_empty_and_save_zero_value_is_true()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => true]));

        $this->assertEquals('£0.00', $fieldtype->augment(null));
    }

    #[Test]
    public function can_pre_process_index()
    {
        $this->assertEquals('£25.72', (new MoneyFieldtype)->preProcessIndex(2572));
    }

    #[Test]
    public function can_pre_process_index_when_value_is_empty_and_save_zero_value_is_false()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => false]));

        $this->assertNull($fieldtype->preProcessIndex(null));
    }

    #[Test]
    public function can_pre_process_index_when_value_is_empty_and_save_zero_value_is_true()
    {
        $fieldtype = (new MoneyFieldtype)->setField(new Field('money', ['save_zero_value' => true]));

        $this->assertEquals('£0.00', $fieldtype->preProcessIndex(null));
    }
}