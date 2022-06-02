<?php

namespace DoubleThreeDigital\SimpleCommerce\UpdateScripts\v3_2;

use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Facades\File;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Collection;
use Statamic\Fields\Blueprint;
use Statamic\UpdateScripts\UpdateScript;

class RenameCouponValueField extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.2.1');
    }

    public function update()
    {
        if (! isset(SimpleCommerce::couponDriver()['collection'])) {
            return;
        }

        // Rename field in blueprint
        Collection::find(SimpleCommerce::couponDriver()['collection'])
            ->entryBlueprints()
            ->filter(function (Blueprint $blueprint) {
                return $blueprint->hasField('value');
            })
            ->each(function (Blueprint $blueprint) {
                $blueprintContents = File::get($blueprint->path());

                $blueprintContents = str_replace('handle: value', 'handle: coupon_value', $blueprintContents);

                File::put($blueprint->path(), $blueprintContents);
            });

        // Change field in coupon entries
        Collection::find(SimpleCommerce::couponDriver()['collection'])
            ->queryEntries()
            ->get()
            ->each(function (Entry $entry) {
                $entry->set('coupon_value', $entry->get('value'));
                $entry->set('value', null);

                $entry->save();
            });

        $this->console()->info('Due to some conflicts with Statamic, Simple Commerce has renamed the `value` field to `coupon_value`.');
    }
}
