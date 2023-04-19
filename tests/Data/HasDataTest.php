<?php

use DoubleThreeDigital\SimpleCommerce\Data\HasData;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;

uses(TestCase::class);
beforeEach(function () {
    $this->trait = new TraitAccess();
});


test('can get all data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
        'fiz' => 'baa',
    ]);

    $data = $this->trait->data();

    $this->assertIsObject($data);

    $this->assertTrue($data->has('foo'));
    $this->assertTrue($data->has('fiz'));
});

test('can set data', function () {
    $data = $this->trait->data([
        'joo' => 'mla',
        'dru' => 'pal',
    ]);

    $this->assertIsObject($data);

    $this->assertArrayHasKey('joo', $this->trait->data->toArray());
    $this->assertArrayHasKey('dru', $this->trait->data->toArray());
});

test('can set data and ensure existing data has been overwritten', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
        'fiz' => 'baa',
    ]);

    $data = $this->trait->data([
        'joo' => 'mla',
        'dru' => 'pal',
    ]);

    $this->assertIsObject($data);

    $this->assertArrayNotHasKey('foo', $this->trait->data->toArray());
    $this->assertArrayNotHasKey('fiz', $this->trait->data->toArray());
    $this->assertArrayHasKey('joo', $this->trait->data->toArray());
    $this->assertArrayHasKey('dru', $this->trait->data->toArray());
});

test('returns true if has data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $has = $this->trait->has('foo');

    $this->assertTrue($has);
});

test('returns false if does not have data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $has = $this->trait->has('bar');

    $this->assertFalse($has);
});

test('can get data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $get = $this->trait->get('foo');

    $this->assertIsString($get);
    $this->assertSame($get, 'bar');
});

test('returns null if data does not exist', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $get = $this->trait->get('bar');

    $this->assertNull($get);
});

test('can set new data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $set = $this->trait->set('bar', 'foo');

    $this->assertArrayHasKey('bar', $this->trait->data->toArray());
    $this->assertSame($this->trait->get('bar'), 'foo');
});

test('can set existing data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $set = $this->trait->set('foo', 'barz');

    $this->assertArrayHasKey('foo', $this->trait->data->toArray());
    $this->assertSame($this->trait->data->get('foo'), 'barz');
});

test('can merge data', function () {
    $this->trait->data = collect([
        'foo' => 'barz',
    ]);

    $set = $this->trait->merge([
        'fiz' => 'baa',
    ]);

    $this->assertArrayHasKey('foo', $this->trait->data->toArray());
    $this->assertArrayHasKey('fiz', $this->trait->data->toArray());

    $this->assertSame($this->trait->data->get('foo'), 'barz');
    $this->assertSame($this->trait->data->get('fiz'), 'baa');
});

test('can get data as array', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $toArray = $this->trait->toArray();

    $this->assertIsArray($toArray);

    $this->assertSame($toArray, [
        'foo' => 'bar',
    ]);
});

// Helpers
function __construct()
{
    test()->data = collect();
}
