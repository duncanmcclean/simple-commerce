<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Rules;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Entry;

class EntryExists implements Rule
{
    public function passes($attribute, $value)
    {
        return Entry::find($value) === null ? false : true;
    }

    public function message()
    {
        return __('simple-commerce::validation.entry_exists');
    }
}
