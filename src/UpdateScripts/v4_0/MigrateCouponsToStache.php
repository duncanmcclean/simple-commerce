<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v4_0;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Statamic\Facades\Collection;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class MigrateCouponsToStache extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('4.0.0-beta.1');
    }

    public function update()
    {
        $couponCollectionHandle = config('simple-commerce.content.coupons.collection', 'coupons');

        Collection::findByHandle($couponCollectionHandle)
            ->queryEntries()
            ->get()
            ->each(function ($entry) {
                $coupon = Coupon::make()
                    ->code($entry->slug())
                    ->type($entry->get('type'))
                    ->value($entry->get('coupon_value') ?? $entry->get('value'))
                    ->data(array_merge(
                        $entry->data()->except([
                            'updated_at',
                            'created_at',
                            'blueprint',
                            'coupon_value',
                            'title',
                        ])->toArray(),
                        [
                            'name' => $entry->get('title'),
                        ]
                    ));

                $coupon->save();
            });

        ConfigWriter::edit('simple-commerce')->replace(
            'content',
            collect(config('simple-commerce.content'))
                ->reject(function ($value, $key) {
                    return $key === 'coupons';
                })
                ->toArray()
        )->save();

        Collection::findByHandle($couponCollectionHandle)->delete();
    }
}
