<?php

namespace DoubleThreeDigital\SimpleCommerce\Repositories;

use DoubleThreeDigital\SimpleCommerce\Contracts\CurrencyRepository as ContractsCurrencyRepository;
use DoubleThreeDigital\SimpleCommerce\Data\Currencies;
use Illuminate\Support\Facades\Config;
use Money\Currencies\ISOCurrencies;
use Money\Currency as MoneyCurrency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;
use NumberFormatter;
use Statamic\Sites\Site;

class CurrencyRepository implements ContractsCurrencyRepository
{
    public function get(Site $site): array
    {
        $siteSettings = collect(Config::get('simple-commerce.sites'))
            ->get($site->handle());

        return Currencies::where('code', $siteSettings['currency'])
            ->first();
    }

    public function parse($price, Site $site): string
    {
        $money = new Money(str_replace('.', '', $price), new MoneyCurrency($this->get($site)['code']));

        $numberFormatter = new NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, new ISOCurrencies());

        return $moneyFormatter->format($money);
    }

    public static function bindings(): array
    {
        return [];
    }
}
