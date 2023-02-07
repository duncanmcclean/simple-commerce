<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use Illuminate\Support\Facades\Artisan;
use Statamic\UpdateScripts\UpdateScript;

class MigrateOrderStatuses extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0-beta.1');
    }

    public function update()
    {
        Artisan::call('sc:migrate-order-statuses');
    }
}
