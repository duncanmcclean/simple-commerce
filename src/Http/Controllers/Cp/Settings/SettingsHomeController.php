<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp\Settings;

use Statamic\Http\Controllers\CP\CpController;

class SettingsHomeController extends CpController
{
    public function index()
    {
        return view('simple-commerce::cp.settings.index', [
            'settings' => [
                [
                    'title'       => 'Order Statuses',
                    'description' => 'Order statuses help you to organise the status of orders in your store.',
                    'url'         => cp_route('settings.order-statuses.index'),
                    'icon'        => 'select',
                ],
                [
                    'title'       => 'Shipping',
                    'description' => 'Manage Shipping Categories and Shipping Methods available to your customers.',
                    'url'         => cp_route('settings.shipping.index'),
                    'icon'        => 'pin',
                ],
                [
                    'title'       => 'Tax Rates',
                    'description' => 'Manage the tax rates that are added to customer\'s orders when checking out.',
                    'url'         => cp_route('settings.tax-rates.index'),
                    'icon'        => 'earth',
                ],
                [
                    'title'       => 'More Settings',
                    'description' => 'Simple Commerce lets you configure more settings in the config/simple-commerce.php file.',
                    'url'         => '#',
                    'icon'        => 'settings-horizontal',
                ],
            ],
        ]);
    }
}
