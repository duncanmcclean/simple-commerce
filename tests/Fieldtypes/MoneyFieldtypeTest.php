<?php

use DuncanMcClean\SimpleCommerce\Fieldtypes\MoneyFieldtype;
use DuncanMcClean\SimpleCommerce\Tests\Fieldtypes\Helpers\MoneyFieldtypeWithMockedField;
use Statamic\Fields\Field;

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

    $preProcess = (new MoneyFieldtype())->preProcess($value);

    expect($preProcess)->toBe('25.50');
});

test('can pre process data where value includes a decimal points', function () {
    $value = '25.99';

    $preProcess = (new MoneyFieldtype())->preProcess($value);

    expect($preProcess)->toBe('25.99');
});

test('can pre process data when value is empty and save_zero_value is false', function () {
    $preProcess = (new MoneyFieldtype())->preProcess(null);

    expect($preProcess)->toBeNull();
});

test('can pre process data when value is empty and save_zero_value is true', function () {
    $preProcess = (new MoneyFieldtype())
        ->setField(new Field('money', ['save_zero_value' => true]))
        ->preProcess(null);

    expect($preProcess)->toBe(0);
});

test('can process data', function () {
    $value = '12.65';

    $process = (new MoneyFieldtype())->process($value);

    expect($process)->toBe(1265);
});

test('can process data when value is empty and save_zero_value is false', function () {
    $process = (new MoneyFieldtype())->process(null);

    expect($process)->toBeNull();
});

test('can process data when value is empty and save_zero_value is true', function () {
    $process = (new MoneyFieldtype())
        ->setField(new Field('money', ['save_zero_value' => true]))
        ->process(null);

    expect($process)->toBe(0);
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

test('can augment data when value is empty and save_zero_value is false', function () {
    $augment = (new MoneyFieldtype())->augment(null);

    expect($augment)->toBe(null);
});

test('can augment data when value is empty and save_zero_value is true', function () {
    $augment = (new MoneyFieldtype())
        ->setField(new Field('money', ['save_zero_value' => true]))
        ->augment(null);

    expect($augment)->toBe('£0.00');
});

test('can get pre process index', function () {
    $value = 2572;

    $preProcessIndex = (new MoneyFieldtype())->preProcessIndex($value);

    expect($preProcessIndex)->toBe('£25.72');
});

test('can get pre process index when value is empty and save_zero_value is false', function () {
    $preProcessIndex = (new MoneyFieldtype())->preProcessIndex(null);

    expect($preProcessIndex)->toBe(null);
});

test('can get pre process index when value is empty and save_zero_value is true', function () {
    $preProcessIndex = (new MoneyFieldtype())
        ->setField(new Field('money', ['save_zero_value' => true]))
        ->preProcessIndex(null);

    expect($preProcessIndex)->toBe('£0.00');
});
