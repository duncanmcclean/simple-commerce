<?php

namespace Damcclean\Commerce\Helpers;

use Damcclean\Commerce\Facades\Product;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class Cart
{
    public function all()
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return collect(request()->session()->get('cart'))
            ->map(function ($item) use ($currencies, $moneyFormatter) {
                $product = Product::findBySlug($item['slug'])->toArray();

                $product['quantity'] = $item['quantity'];
                $product['price'] = $product['price'] * $product['quantity'];

                $product['price'] = $moneyFormatter->format(Money::{strtoupper(config('commerce.currency.code'))}(($product['price'] * 100) * $product['quantity']));

                return collect($product);
            });

    }

//    public function get()
//    {
//
//    }
//
//    public function put()
//    {
//
//    }

    public function replace($items)
    {
        return request()->session()->put('cart', $items);
    }

    public function remove($slug)
    {
        $items = collect($this->cart->all())
            ->reject(function ($product) use ($slug) {
                if ($product['slug'] == $slug) {
                    return true;
                }

                return false;
            });

        return $this->replace($items);
    }

    public function total()
    {
        $total = 0;

        $this->all()
            ->each(function ($item) use (&$total) {
                $total += $item['price'];
            });

        $amount = Money::{strtoupper(config('commerce.currency.code'))}($total * 100);
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);
        return $moneyFormatter->format($amount);
    }

    public function count()
    {
        return $this->all()->count();
    }

    public function clear()
    {
        return request()->session()->forget('cart');
    }
}
