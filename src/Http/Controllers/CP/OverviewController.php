<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\CP;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\CP\OverviewRequest;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Overview;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Facades\Collection;
use Statamic\Facades\User;

class OverviewController
{
    public function index(OverviewRequest $request)
    {
        if ($request->wantsJson()) {
            $data = collect($request->get('widgets'))
                ->mapWithKeys(function ($widgetHandle) use ($request) {
                    $widget = Overview::widget($widgetHandle);

                    return [
                        $widgetHandle => $widget['callback']($request),
                    ];
                })
                ->toArray();

            return ['data' => $data];
        }

        $showEntriesWarning = User::current()->isSuper()
            && $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)
            && Collection::find(SimpleCommerce::orderDriver()['collection'])->queryEntries()->count() > 5000;

        return view('simple-commerce::cp.overview', [
            'widgets' => Overview::widgets(),
            'showEntriesWarning' => $showEntriesWarning,
        ]);
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
