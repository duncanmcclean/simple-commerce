<?php

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\SetupCollections;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Statamic\Facades\Collection;
use Statamic\Fields\Field;

uses(TestCase::class);

test('can preload currency with no field', function () {
    $preload = (new MoneyFieldtype())->preload();

    expect($preload)->toBeArray();
    $this->assertArrayHasKey('code', $preload);
    $this->assertArrayHasKey('name', $preload);
    $this->assertArrayHasKey('symbol', $preload);
});

test('can preload currency with field', function () {
    $preload = (new MoneyFieldtypeWithMockedField())->preload();

    expect($preload)->toBeArray();
    $this->assertArrayHasKey('code', $preload);
    $this->assertArrayHasKey('name', $preload);
    $this->assertArrayHasKey('symbol', $preload);
});

test('can pre process data', function () {
    $value = 2550;

    $process = (new MoneyFieldtype())->preProcess($value);

    expect($process)->toBe('25.50');
});

test('can process data', function () {
    $value = '12.65';

    $process = (new MoneyFieldtype())->process($value);

    expect($process)->toBe(1265);
});

test('has a title', function () {
    $title = (new MoneyFieldtype())->title();

    expect($title)->toBe('Money');
});

test('has a component', function () {
    $title = (new MoneyFieldtype())->component();

    expect($title)->toBe('money');
});

test('can augment data', function () {
    $value = 1945;

    $augment = (new MoneyFieldtype())->augment($value);

    expect($augment)->toBe('£19.45');
});

test('can augment data when value is null', function () {
    $value = null;

    $augment = (new MoneyFieldtype())->augment($value);

    expect($augment)->toBe(null);
});

test('can get pre process index', function () {
    $value = 2572;

    $augment = (new MoneyFieldtype())->preProcessIndex($value);

    expect($augment)->toBe('£25.72');
});

// Helpers
function field(): ?Field
{
    test()->setupProducts();

    $products = Collection::findByHandle('products');

    return (new Field('price', [
        'read_only' => false,
    ]))->setParent($products)->setValue(1599);
}
