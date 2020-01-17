<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;

class Currency
{
    public function primary()
    {
        return CurrencyModel::where('iso', config('commerce.currency'))->first();
    }

    public function parse(int $total)
    {
        $symbol = $this->primary()->symbol;
        $total = number_format($total, 2, '.', config('commerce.currency_separator'));

        switch (config('commerce.currency_position')) {
            case 'left':
                return $symbol.$total;

            case 'right':
                return $total.$symbol;
        }
    }
}
