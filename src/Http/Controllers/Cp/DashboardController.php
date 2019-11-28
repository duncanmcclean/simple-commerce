<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Statamic\Contracts\Auth\User;
use Statamic\Extend\Management\WidgetLoader;
use Statamic\Facades\Preference;
use Statamic\Http\Controllers\CP\CpController;

class DashboardController extends CpController
{
    public function __invoke(WidgetLoader $loader)
    {
        return view('commerce::cp.dashboard');
    }
}
