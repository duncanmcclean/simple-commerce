<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\Settings;

use DoubleThreeDigital\SimpleCommerce\Models\TaxRate;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class TaxRateController extends CpController
{
    public function index()
    {
        $blueprint = (new TaxRate())->blueprint();
        $fields = $blueprint->fields()->preProcess();

        return view('simple-commerce::cp.settings.tax-rates', [
            'crumbs'    => Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Settings', 'link' => cp_route('settings.index')]]),
            'blueprint' => $blueprint->toPublishArray(),
            'meta'      => $fields->meta(),
            'values'    => $fields->values(),
        ]);
    }
}
