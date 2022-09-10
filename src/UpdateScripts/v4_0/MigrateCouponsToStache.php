<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v4_0;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use Illuminate\Support\Str;
use Statamic\Facades\Collection;
use Statamic\UpdateScripts\UpdateScript;
use Stillat\Proteus\Support\Facades\ConfigWriter;

class MigrateCouponsToStache extends UpdateScript
{
    protected $couponCollectionHandle;

    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('4.0.0-beta.1');
    }

    public function update()
    {
        $this->couponCollectionHandle = config('simple-commerce.content.coupons.collection', 'coupons');

        $this
            ->migrateCouponEntriesToStache()
            ->updateConfig()
            ->deleteCouponCollection();
    }

    protected function migrateCouponEntriesToStache(): self
    {
        Collection::findByHandle($this->couponCollectionHandle)
            ->queryEntries()
            ->get()
            ->each(function ($entry) {
                $coupon = Coupon::make()
                    ->code(Str::upper($entry->slug()))
                    ->type($entry->get('type'))
                    ->value($entry->get('coupon_value') ?? $entry->get('value'))
                    ->enabled($entry->get('enabled') ?? true)
                    ->data(array_merge(
                        $entry->data()->except([
                            'updated_at',
                            'created_at',
                            'blueprint',
                            'coupon_value',
                            'title',
                        ])->toArray(),
                        [
                            'description' => $entry->get('title'),
                        ]
                    ));

                $coupon->save();
            });

        return $this;
    }

    protected function updateConfig(): self
    {
        ConfigWriter::edit('simple-commerce')->replace(
            'content',
            collect(config('simple-commerce.content'))
                ->reject(function ($value, $key) {
                    return $key === 'coupons';
                })
                ->toArray()
        )->save();

        return $this;
    }

    protected function deleteCouponCollection(): self
    {
        Collection::findByHandle($this->couponCollectionHandle)->delete();

        return $this;
    }
}
