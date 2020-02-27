<?php

namespace DoubleThreeDigital\SimpleCommerce\Helpers;

use DoubleThreeDigital\SimpleCommerce\Models\Currency as CurrencyModel;

class Currency
{
    public function primary()
    {
        if (config('simple-commerce.currency.iso') === null) {
            throw new \Exception('Please configure your store\'s currency.');
        }

        return CurrencyModel::where('iso', config('simple-commerce.currency.iso'))->first();
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
            $total = number_format($total, 2, '.', config('simple-commerce.currency.separator'));
        }

        if ($showSymbol == true) {
            $symbol = $this->primary()->symbol;

            if (config('simple-commerce.currency.position') === 'left') {
                $total = $symbol.$total;
            }

            if (config('simple-commerce.currency.position') === 'right') {
                $total = $total.$symbol;
            }
        }

        return $total;
    }
}
