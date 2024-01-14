<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v6_0;

use Illuminate\Support\Facades\Artisan;
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
