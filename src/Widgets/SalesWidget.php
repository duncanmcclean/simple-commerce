<?php

namespace DoubleThreeDigital\SimpleCommerce\Widgets;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Order;
use Illuminate\Support\Collection;
use Statamic\Entries\Entry as AnEntry;
use Statamic\Facades\Site;
use Statamic\Widgets\Widget;

class SalesWidget extends Widget
{
    public function html()
    {
        $baseQuery = Order::query()
            ->filter(function (AnEntry $entry) {
                return $entry->has('paid_date');
            });

        $sevenDays = $baseQuery
            ->filter(function (AnEntry $entry) {
                return Carbon::parse($entry->get('paid_date')) >= Carbon::now()->subDays(7);
            });

        $fourteenDays = $baseQuery
            ->filter(function (AnEntry $entry) {
                return Carbon::parse($entry->get('paid_date')) >= Carbon::now()->subDays(14);
            });

        $thirtyDays = $baseQuery
            ->filter(function (AnEntry $entry) {
                return Carbon::parse($entry->get('paid_date')) >= Carbon::now()->subDays(30);
            });

        return view('simple-commerce::widgets.sales-widget', [
            'sevenDays'    => [
                'count' => $sevenDays->count(),
                'total' => $this->getTotal($sevenDays),
            ],
            'fourteenDays' => [
                'count' => $fourteenDays->count(),
                'total' => $this->getTotal($fourteenDays),
            ],
            'thirtyDays'   => [
                'count' => $thirtyDays->count(),
                'total' => $this->getTotal($thirtyDays),
            ],
        ]);
    }

    protected function getTotal(Collection $ordersCollection)
    {
        $total = 0;

        $ordersCollection
            ->each(function ($order) use (&$total) {
                if (! $order->grandTotal()) {
                    return;
                }

                $total = $total + $order->grandTotal();
            });

        return Currency::parse($total, Site::current());
    }
}
