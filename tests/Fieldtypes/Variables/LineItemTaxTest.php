<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fieldtypes\Variables;

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables\LineItemTax;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class LineItemTaxTest extends TestCase
{
    /** @test */
    public function can_augment_line_item_tax()
    {
        $value = [
            'amount' => 1564,
            'rate' => 20,
            'price_includes_tax' => true,
        ];

        $augment = (new LineItemTax())->augment($value);

        $this->assertIsArray($augment);

        $this->assertArrayHasKey('amount', $augment);
        $this->assertArrayHasKey('rate', $augment);
        $this->assertArrayHasKey('price_includes_tax', $augment);

        $this->assertSame($augment['amount'], '£15.64');
        $this->assertSame($augment['rate'], 20);
        $this->assertSame($augment['price_includes_tax'], true);
    }
}
