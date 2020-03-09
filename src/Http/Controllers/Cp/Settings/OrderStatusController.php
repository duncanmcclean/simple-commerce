<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\Settings;

use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class OrderStatusController extends CpController
{
    public function index()
    {
        $blueprint = (new OrderStatus())->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.settings.order-statuses', [
            'crumbs'    => Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Settings', 'link' => cp_route('settings.index')]]),
            'blueprint' => $blueprint->toPublishArray(),
            'meta'      => $fields->meta(),
            'values'    => $fields->values(),
        ]);
    }
}
