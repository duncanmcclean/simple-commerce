<?php

namespace DoubleThreeDigital\SimpleCommerce\Support\Rules;

use Illuminate\Contracts\Validation\Rule;
use Statamic\Facades\Entry;

class EntryExists implements Rule
{
    // TODO: check we're not using this rule where it could cause problems

    public function passes($attribute, $value)
    {
        return Entry::find($value) === null
            ? false : true;
    }

    public function message()
    {
        return __('simple-commerce::validation.entry_exists');
    }
}
