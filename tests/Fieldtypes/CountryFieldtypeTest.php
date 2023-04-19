<?php

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

uses(TestCase::class);
beforeEach(function () {
    $this->fieldtype = new CountryFieldtype;
});


test('can get index items', function () {
    $getIndexItems = $this->fieldtype->getIndexItems(new Request());

    $this->assertTrue($getIndexItems instanceof Collection);

    $this->assertSame($getIndexItems->last(), [
        'id' => 'ZW',
        'iso' => 'ZW',
        'name' => 'Zimbabwe',
    ]);
});

test('can get columns', function () {
    $getColumns = (new Invader($this->fieldtype))->getColumns();

    $this->assertIsArray($getColumns);

    $this->assertTrue($getColumns[0] instanceof Column);
    $this->assertSame($getColumns[0]->field(), 'name');
    $this->assertSame($getColumns[0]->label(), 'Name');

    $this->assertTrue($getColumns[1] instanceof Column);
    $this->assertSame($getColumns[1]->field(), 'iso');
    $this->assertSame($getColumns[1]->label(), 'ISO Code');
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray('GB');

    $this->assertIsArray($toItemArray);

    $this->assertSame($toItemArray, [
        'id' => 'GB',
        'title' => 'United Kingdom',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex('GB');

    $this->assertIsString($preProcessIndex);
    $this->assertSame($preProcessIndex, 'United Kingdom');
});

test('can preprocess index with no country', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    $this->assertNull($preProcessIndex);
});

test('can preprocess with multiple countries', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(['GB', 'US']);

    $this->assertIsString($preProcessIndex);
    $this->assertSame($preProcessIndex, 'United Kingdom, United States');
});
