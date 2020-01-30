<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;

class Currency
{
    public function primary()
    {
        return CurrencyModel::where('iso', config('commerce.currency'))->first();
    }

    public function symbol()
    {
        return $this->primary()->symbol;
    }

    public function iso()
    {
        return $this->primary()->iso;
    }

    public function parse(int $total, bool $showSeparator = true, bool $showSymbol = true)
    {
        if ($showSeparator == true) {
            $total = number_format($total, 2, '.', config('commerce.currency_separator'));
        }

        if ($showSymbol == true) {
            $symbol = $this->primary()->symbol;

            if (config('commerce.currency_position') === 'left') {
                $total = $symbol.$total;
            }

            if (config('commerce.currency_position') === 'right') {
                $total = $total.$symbol;
            }
        }

        return $total;
    }

    public function unparse(string $total)
    {
        return (int) str_replace($this->primary()->symbol, '', $total);
    }
}
