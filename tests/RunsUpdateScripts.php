<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests;

trait RunsUpdateScripts
{
    /**
     * Run update script in your tests without checking package version.
     *
     * @param  string  $fqcn
     * @param  string  $package
     */
    protected function runUpdateScript($fqcn, $package = 'doublethreedigital/simple-commerce')
    {
        $script = new $fqcn($package);

        $script->update();
    }
}
