<?php

use DuncanMcClean\SimpleCommerce\Fieldtypes\RegionFieldtype;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\Invader;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Statamic\CP\Column;

beforeEach(function () {
    $this->fieldtype = new RegionFieldtype;
});

test('can get index items', function () {
    $getIndexItems = $this->fieldtype->getIndexItems(new Request());

    expect($getIndexItems instanceof Collection)->toBeTrue();

    $this->assertSame($getIndexItems->last(), [
        'id' => 'zw-mw',
        'country_iso' => 'ZW',
        'country_name' => 'Zimbabwe',
        'name' => 'Mashonaland West',
        'title' => 'Mashonaland West',
    ]);
});

test('can get columns', function () {
    $getColumns = (new Invader($this->fieldtype))->getColumns();

    expect($getColumns)->toBeArray();

    expect($getColumns[0] instanceof Column)->toBeTrue();
    expect('name')->toBe($getColumns[0]->field());
    expect('Name')->toBe($getColumns[0]->label());

    expect($getColumns[1] instanceof Column)->toBeTrue();
    expect('country_name')->toBe($getColumns[1]->field());
    expect('Country')->toBe($getColumns[1]->label());
});

test('can return as item array', function () {
    $toItemArray = $this->fieldtype->toItemArray('gb-sct');

    expect($toItemArray)->toBeArray();

    $this->assertSame($toItemArray, [
        'id' => 'gb-sct',
        'title' => 'Scotland',
    ]);
});

test('can preprocess index', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex('gb-sct');

    expect($preProcessIndex)->toBeString();
    expect('Scotland')->toBe($preProcessIndex);
});

test('can preprocess index with no region', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(null);

    expect($preProcessIndex)->toBeNull();
});

test('can preprocess with multiple regions', function () {
    $preProcessIndex = $this->fieldtype->preProcessIndex(['gb-sct', 'gb-wls']);

    expect($preProcessIndex)->toBeString();
    expect('Scotland, Wales')->toBe($preProcessIndex);
});

it('can augment region', function () {
    $augment = $this->fieldtype->augment('gb-sct');

    expect($augment)->toBeArray();
    expect($augment['id'])->toBe('gb-sct');
    expect($augment['name'])->toBe('Scotland');
});
