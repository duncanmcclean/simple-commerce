<?php

namespace DuncanMcClean\SimpleCommerce\UpdateScripts\v6_0;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Statamic\UpdateScripts\UpdateScript;

class ReplaceOldVendorName extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('6.0.0');
    }

    public function update()
    {
        $configFileContents = File::get(config_path('simple-commerce.php'));

        $configFileContents = Str::of($configFileContents)
            ->replace("DoubleThreeDigital", "DuncanMcClean")
            ->toString();

        File::put(config_path('simple-commerce.php'), $configFileContents);
    }
}
