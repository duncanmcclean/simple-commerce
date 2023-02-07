<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v5_0;

use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class UpdateNotificationsConfig extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('5.0.0-beta.1');
    }

    public function update()
    {
        if (is_array(config('simple-commerce.notifications.order_shipped'))) {
            ConfigWriter::edit('simple-commerce')
                ->remove('notifications.order_shipped')
                ->set('notifications.order_dispatched', config('simple-commerce.notifications.order_shipped'))
                ->save();

            $this->console()->info('Simple Commerce has updated your notifications config. The order_shipped event has been renamed to order_dispatched.');
        }
    }
}
