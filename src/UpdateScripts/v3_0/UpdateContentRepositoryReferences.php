<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v3_0;

use DoubleThreeDigital\SimpleCommerce\Coupons\EntryCouponRepository;
use DoubleThreeDigital\SimpleCommerce\Customers\EntryCustomerRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Products\EntryProductRepository;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class UpdateContentRepositoryReferences extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.0.0-beta.1');
    }

    public function update()
    {
        ConfigWriter::edit('simple-commerce')
            ->remove('content.coupons.driver')
            ->remove('content.customers.driver')
            ->remove('content.orders.driver')
            ->remove('content.products.driver')
            ->set('content.coupons.repository', EntryCouponRepository::class)
            ->set('content.customers.repository', EntryCustomerRepository::class)
            ->set('content.orders.repository', EntryOrderRepository::class)
            ->set('content.products.repository', EntryProductRepository::class)
            ->save();

        $this->console()->info('Simple Commerce has updated your config file to point to the new content repositories.');
    }
}
