<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v4_0;

use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Str;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
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
            // ->migrateCouponEntriesToStache()
            ->updateOrderBlueprint();
        // ->updateConfig()
        // ->deleteCouponCollection();
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

    protected function updateOrderBlueprint(): self
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            Collection::find(SimpleCommerce::orderDriver()['collection'])
                ->entryBlueprints()
                ->each(function (Blueprint $blueprint) {
                    $blueprint->ensureFieldHasConfig('coupon', [
                        'max_items' => 1,
                        'mode' => 'default',
                        'display' => 'Coupon',
                        'type' => 'coupon',
                        'read_only' => true,
                    ]);

                    $blueprint->save();
                });
        }

        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            $this->console()->warn('Please change the Coupon field in your Order blueprint from an Entries field to a Coupons field.');
        }

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

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
