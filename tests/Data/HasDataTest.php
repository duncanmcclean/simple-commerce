<?php

use DuncanMcClean\SimpleCommerce\Tests\Data\TraitAccess;

beforeEach(function () {
    $this->trait = new TraitAccess();
});

test('can get all data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
        'fiz' => 'baa',
    ]);

    $data = $this->trait->data();

    expect($data)->toBeObject();

    expect($data->has('foo'))->toBeTrue();
    expect($data->has('fiz'))->toBeTrue();
});

test('can set data', function () {
    $data = $this->trait->data([
        'joo' => 'mla',
        'dru' => 'pal',
    ]);

    expect($data)->toBeObject();

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

    expect($data)->toBeObject();

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

    expect($has)->toBeTrue();
});

test('returns false if does not have data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $has = $this->trait->has('bar');

    expect($has)->toBeFalse();
});

test('can get data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $get = $this->trait->get('foo');

    expect($get)->toBeString();
    expect('bar')->toBe($get);
});

test('returns null if data does not exist', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $get = $this->trait->get('bar');

    expect($get)->toBeNull();
});

test('can set new data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $set = $this->trait->set('bar', 'foo');

    $this->assertArrayHasKey('bar', $this->trait->data->toArray());
    expect('foo')->toBe($this->trait->get('bar'));
});

test('can set existing data', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $set = $this->trait->set('foo', 'barz');

    $this->assertArrayHasKey('foo', $this->trait->data->toArray());
    expect('barz')->toBe($this->trait->data->get('foo'));
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

    expect('barz')->toBe($this->trait->data->get('foo'));
    expect('baa')->toBe($this->trait->data->get('fiz'));
});

test('can get data as array', function () {
    $this->trait->data = collect([
        'foo' => 'bar',
    ]);

    $toArray = $this->trait->toArray();

    expect($toArray)->toBeArray();

    $this->assertSame($toArray, [
        'foo' => 'bar',
    ]);
});
