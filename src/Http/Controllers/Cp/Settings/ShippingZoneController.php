<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\Settings;

use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class ShippingZoneController extends CpController
{
    public function index()
    {
        $blueprint = Blueprint::find('simple-commerce/shipping_zone');
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('commerce::cp.settings.shipping-zones', [
            'crumbs' => Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Settings', 'link' => cp_route('settings.index')]]),
            'blueprint' => $blueprint->toPublishArray(),
            'meta' => $fields->meta(),
            'values' => $fields->values(),
        ]);
    }
}
