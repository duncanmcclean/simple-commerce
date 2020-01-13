<?php

namespace Damcclean\Commerce\Helpers;

use Damcclean\Commerce\Models\Currency as CurrencyModel;

class Currency
{
    public function primary()
    {
        return CurrencyModel::where('primary', true)->first();
    }
}
