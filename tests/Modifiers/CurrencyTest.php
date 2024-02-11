<?php

use DuncanMcClean\SimpleCommerce\Modifiers\Currency as CurrencyModifier;

beforeEach(function () {
    $this->modifier = new CurrencyModifier;
});

test('can convert value into currency string', function () {
    $modifier = $this->modifier->index(1557, [], []);

    expect('£15.57')->toBe($modifier);
});
