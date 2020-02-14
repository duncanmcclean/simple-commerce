<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\Settings;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\SettingsUpdateRequest;
use Illuminate\Support\Facades\Config;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class SettingsHomeController extends CpController
{
    public function index()
    {
        return view('commerce::cp.settings.index', [
            'crumbs' => Breadcrumbs::make([['text' => 'Simple Commerce']]),
            'settings' => [
                [
                    'title' => 'Order Statuses',
                    'description' => 'Order statuses help you to organise the status of orders in your store.',
                    'url' => cp_route('settings.order-statuses.index'),
                ],
                [
                    'title' => 'Shipping Zones',
                    'description' => 'Shipping Zones gives you the ability to charge customers a fixed price for shipping to them.',
                    'url' => cp_route('settings.shipping-zones.index'),
                ],
                [
                    'title' => 'Tax Rates',
                    'description' => 'Manage the tax rates that are added to customer\'s orders when checking out.',
                    'url' => cp_route('settings.tax-rates.index'),
                ],
            ],
        ]);
    }
}
