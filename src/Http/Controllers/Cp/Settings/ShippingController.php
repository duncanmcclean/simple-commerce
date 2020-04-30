<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\Settings;

use DoubleThreeDigital\SimpleCommerce\Models\ShippingZone;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class ShippingController extends CpController
{
    public function index()
    {
        $blueprint = (new ShippingZone())->blueprint();
        $fields = $blueprint->fields()->preProcess();

        return view('simple-commerce::cp.settings.shipping', [
            'crumbs' => Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Settings', 'link' => cp_route('settings.index')]]),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
        ]);
    }
}
