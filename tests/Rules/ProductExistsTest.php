<?php

use DuncanMcClean\SimpleCommerce\Rules\ProductExists;
use Illuminate\Support\Facades\Validator;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

it('passes if entry exists', function () {
    Collection::make('products')->save();

    $entry = Entry::make()
        ->collection('products');

    $entry->save();

    $validate = Validator::make([
        'entry' => $entry->id(),
    ], [
        'entry' => [new ProductExists()],
    ]);

    expect($validate->fails())->toBeFalse();
});

it('fails if entry does not exist', function () {
    $validate = Validator::make([
        'entry' => 'wippers',
    ], [
        'entry' => [new ProductExists()],
    ]);

    expect($validate->fails())->toBeTrue();
});
