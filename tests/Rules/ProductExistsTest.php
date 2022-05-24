<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Rules;

use DoubleThreeDigital\SimpleCommerce\Rules\ProductExists;
use DoubleThreeDigital\SimpleCommerce\Tests\TestCase;
use Illuminate\Support\Facades\Validator;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;

class ProductExistsTest extends TestCase
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
            'entry' => [new ProductExists()],
        ]);

        $this->assertFalse($validate->fails());
    }

    /** @test */
    public function it_fails_if_entry_does_not_exist()
    {
        $validate = Validator::make([
            'entry' => 'wippers',
        ], [
            'entry' => [new ProductExists()],
        ]);

        $this->assertTrue($validate->fails());
    }
}
