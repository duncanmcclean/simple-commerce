<?php

use DoubleThreeDigital\SimpleCommerce\Fieldtypes\ShippingMethodFieldtype;
use DoubleThreeDigital\SimpleCommerce\Shipping\FreeShipping;
use DoubleThreeDigital\SimpleCommerce\Tests\Helpers\Invader;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

uses(TestCase::class);
beforeEach(function () {
    $this->fieldtype = new ShippingMethodFieldtype;
});


test('can get config field items', function () {
    $configFieldItems = (new Invader($this->fieldtype))->configFieldItems();

    $this->assertIsArray($configFieldItems);
});

test('can get index items', function () {
    $getIndexItems = $this->fieldtype->getIndexItems(new Request());

    $this->assertTrue($getIndexItems instanceof Collection);

    $this->assertSame($getIndexItems->last(), [
        'id' => FreeShipping::class,
        'name' => 'Free Shipping',
        'title' => 'Free Shipping',
    ]);
});

test('can get columns', function () {
    $getColumns = (new Invader($this->fieldtype))->getColumns();

    $this->assertIsArray($getColumns);

    $this->assertTrue($getColumns[0] instanceof Column);
    $this->assertSame($getColumns[0]->field(), 'name');
    $this->assertSame($getColumns[0]->label(), 'Name');
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray(FreeShipping::class);

    $this->assertIsArray($toItemArray);

    $this->assertSame($toItemArray, [
        'id' => FreeShipping::class,
        'title' => 'Free Shipping',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(FreeShipping::class);

    $this->assertIsString($preProcessIndex);
    $this->assertSame($preProcessIndex, 'Free Shipping');
});

test('can preprocess index with no shipping method', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    $this->assertNull($preProcessIndex);
});
