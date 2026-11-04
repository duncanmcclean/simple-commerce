<?php

use DuncanMcClean\SimpleCommerce\Tests\Helpers\RefreshContent;
use DuncanMcClean\SimpleCommerce\Tests\Helpers\SetupCollections;
use Illuminate\Support\Facades\File;
use Statamic\Facades\Entry;

uses(RefreshContent::class, SetupCollections::class);

it('removes entries from the configured content directory', function ($collection) {
    $entry = Entry::make()
        ->collection($collection)
        ->slug('test-entry')
        ->data(['title' => 'Test Entry']);

    $entry->save();

    expect(File::exists($entry->path()))->toBeTrue();

    $this->refreshContent();

    expect(File::exists($entry->path()))->toBeFalse();
    expect(Entry::find($entry->id()))->toBeNull();
})->with(['customers', 'orders', 'products']);
