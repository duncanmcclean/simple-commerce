<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use Statamic\Entries\Entry as EntriesEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Widgets\Widget;

class RevenueWidget extends Widget
{
    public function html()
    {
        $lastMonth = $this->orderQuery()
            ->where('paid_date', '>=', now()->subMonth())
            ->sum('grand_total');  

        return view('simple-commerce::revenue-widget', [
            'last_month' => Currency::parse($lastMonth, Site::current()),
        ]);
    }

    protected function orderQuery()
    {
        return Entry::whereCollection('orders')
            ->map(function (EntriesEntry $entry) {
                return [
                    'id' => $entry->id(),
                    'paid_date' => isset($entry->data()['paid_date']) ? Carbon::parse($entry->data()->get('paid_date')) : null,
                    'grand_total' => $entry->data()['grand_total'] ?? 0,
                ];
            })
            ->reject(function ($entry) {
                return $entry['paid_date'] === null;
            });
    }
}