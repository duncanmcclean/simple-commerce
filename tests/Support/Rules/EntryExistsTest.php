<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Rules;

use DoubleThreeDigital\SimpleCommerce\Rules\EntryExists;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class EntryExistsTest extends TestCase
{
    /** @test */
    public function it_passes_if_entry_exists()
    {
        Collection::make('products')->save();

        $entry = Entry::make()
            ->collection('products');

        $entry->save();

        $validate = Validator::make([
            'entry' => $entry->id(),
        ], [
            'entry' => [new EntryExists()],
        ]);

        $this->assertFalse($validate->fails());
    }

    /** @test */
    public function it_fails_if_entry_does_not_exist()
    {
        $validate = Validator::make([
            'entry' => 'wippers',
        ], [
            'entry' => [new EntryExists()],
        ]);

        $this->assertTrue($validate->fails());
    }
}
