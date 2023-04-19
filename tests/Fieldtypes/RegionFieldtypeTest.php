<?php

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\RegionFieldtype;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

uses(TestCase::class);
beforeEach(function () {
    $this->fieldtype = new RegionFieldtype;
});


test('can get index items', function () {
    $getIndexItems = $this->fieldtype->getIndexItems(new Request());

    $this->assertTrue($getIndexItems instanceof Collection);

    $this->assertSame($getIndexItems->last(), [
        'id' => 'zw-mw',
        'country_iso' => 'ZW',
        'country_name' => 'Zimbabwe',
        'name' => 'Mashonaland West',
    ]);
});

test('can get columns', function () {
    $getColumns = (new Invader($this->fieldtype))->getColumns();

    $this->assertIsArray($getColumns);

    $this->assertTrue($getColumns[0] instanceof Column);
    $this->assertSame($getColumns[0]->field(), 'name');
    $this->assertSame($getColumns[0]->label(), 'Name');

    $this->assertTrue($getColumns[1] instanceof Column);
    $this->assertSame($getColumns[1]->field(), 'country_name');
    $this->assertSame($getColumns[1]->label(), 'Country');
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray('gb-sct');

    $this->assertIsArray($toItemArray);

    $this->assertSame($toItemArray, [
        'id' => 'gb-sct',
        'title' => 'Scotland',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex('gb-sct');

    $this->assertIsString($preProcessIndex);
    $this->assertSame($preProcessIndex, 'Scotland');
});

test('can preprocess index with no region', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    $this->assertNull($preProcessIndex);
});

test('can preprocess with multiple regions', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(['gb-sct', 'gb-wls']);

    $this->assertIsString($preProcessIndex);
    $this->assertSame($preProcessIndex, 'Scotland, Wales');
});
