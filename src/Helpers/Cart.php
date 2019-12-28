<?php

namespace Damcclean\Commerce\Helpers;

use Damcclean\Commerce\Facades\Product;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class Cart
{
    public function query()
    {
        $currencies = new ISOCurrencies();
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        return collect(request()->session()->get('cart'))
            ->map(function ($item) use ($currencies, $moneyFormatter) {
                $product = Product::findBySlug($item['slug']);

                return array_merge(collect($product)->toArray(), [
                    'quantity' => $item['quantity'],
                    'price' => $moneyFormatter->format(Money::{strtoupper(config('commerce.currency.code'))}(($product['price'] * 100) * $item['quantity']))
                ]);
            });
    }

    public function all()
    {
        return $this->query();
    }

    public function add(string $slug, int $quantity)
    {
        $items = $this->all();

        foreach ($items as $item) {
            // TODO: Refactor this using collections

            if ($item['slug'] == $slug) {
                $item['quantity'] += $quantity;

                return $this->replace($items);
            }
        }

        $items[] = [
            'slug' => $slug,
            'quantity' => $quantity,
        ];

        return $this->replace($items);
    }

    public function replace($items)
    {
        return request()->session()->put('cart', $items);
    }

    public function remove($slug)
    {
        $items = collect($this->query())
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
        return $this->query()->count();
    }

    public function clear()
    {
        return request()->session()->forget('cart');
    }
}
