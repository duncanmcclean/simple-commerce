<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Modifiers;

use DoubleThreeDigital\SimpleCommerce\Modifiers\Currency as CurrencyModifier;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

class CurrencyTest extends TestCase
{
    protected $modifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->modifier = new CurrencyModifier();
    }

    /** @test */
    public function can_convert_value_into_currency_string()
    {
        $modifier = $this->modifier->index(1557, [], []);

        $this->assertSame($modifier, '£15.57');
    }
}
