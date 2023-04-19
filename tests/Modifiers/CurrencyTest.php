<?php

use DoubleThreeDigital\SimpleCommerce\Modifiers\Currency as CurrencyModifier;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

uses(TestCase::class);
beforeEach(function () {
    $this->modifier = new CurrencyModifier;
});


test('can convert value into currency string', function () {
    $modifier = $this->modifier->index(1557, [], []);

    $this->assertSame($modifier, '£15.57');
});
