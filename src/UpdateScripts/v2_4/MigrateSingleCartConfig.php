<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v2_4;

use Statamic\Facades\Site;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class MigrateSingleCartConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('2.4.0-beta.1')
            && Site::hasMultiple();
    }

    public function update()
    {
        ConfigWriter::write('simple-commerce.cart.single_cart', true);

        $this->console()->info("We've set the 'single_cart' config value to true, for backwards compatibility reasons (due to you running multi-site).");
    }
}
