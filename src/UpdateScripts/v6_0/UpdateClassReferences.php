<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v6_0;

use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\OrderModel;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\Artisan;
use Statamic\Facades\Collection;
use Statamic\UpdateScripts\UpdateScript;

class UpdateClassReferences extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        Artisan::call('sc:update-class-references');
    }
}
