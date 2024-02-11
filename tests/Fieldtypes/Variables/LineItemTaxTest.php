<?php

use DuncanMcClean\SimpleCommerce\Fieldtypes\Variables\LineItemTax;

test('can augment line item tax', function () {
    $value = [
        'amount' => 1564,
        'rate' => 20,
        'price_includes_tax' => true,
    ];

    $augment = (new LineItemTax())->augment($value);

    expect($augment)->toBeArray();

    $this->assertArrayHasKey('amount', $augment);
    $this->assertArrayHasKey('rate', $augment);
    $this->assertArrayHasKey('price_includes_tax', $augment);

    expect('£15.64')->toBe($augment['amount']);
    expect(20)->toBe($augment['rate']);
    expect(true)->toBe($augment['price_includes_tax']);
});
