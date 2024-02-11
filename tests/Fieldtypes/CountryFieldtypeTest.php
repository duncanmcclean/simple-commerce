<?php

use DuncanMcClean\SimpleCommerce\Fieldtypes\CountryFieldtype;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\Invader;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

beforeEach(function () {
    $this->fieldtype = new CountryFieldtype;
});

test('can get index items', function () {
    $getIndexItems = $this->fieldtype->getIndexItems(new Request());

    expect($getIndexItems instanceof Collection)->toBeTrue();

    $this->assertSame($getIndexItems->last(), [
        'id' => 'ZW',
        'iso' => 'ZW',
        'name' => 'Zimbabwe',
        'title' => 'Zimbabwe',
    ]);
});

test('can get columns', function () {
    $getColumns = (new Invader($this->fieldtype))->getColumns();

    expect($getColumns)->toBeArray();

    expect($getColumns[0] instanceof Column)->toBeTrue();
    expect('name')->toBe($getColumns[0]->field());
    expect('Name')->toBe($getColumns[0]->label());

    expect($getColumns[1] instanceof Column)->toBeTrue();
    expect('iso')->toBe($getColumns[1]->field());
    expect('ISO Code')->toBe($getColumns[1]->label());
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray('GB');

    expect($toItemArray)->toBeArray();

    $this->assertSame($toItemArray, [
        'id' => 'GB',
        'title' => 'United Kingdom',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex('GB');

    expect($preProcessIndex)->toBeString();
    expect('United Kingdom')->toBe($preProcessIndex);
});

test('can preprocess index with no country', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    expect($preProcessIndex)->toBeNull();
});

test('can preprocess with multiple countries', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(['GB', 'US']);

    expect($preProcessIndex)->toBeString();
    expect('United Kingdom, United States')->toBe($preProcessIndex);
});

test('can augment country', function () {
    $augment = $this->fieldtype->augment('GB');

    expect($augment)->toBeArray();
    expect($augment['iso'])->toBe('GB');
    expect($augment['name'])->toBe('United Kingdom');
});
