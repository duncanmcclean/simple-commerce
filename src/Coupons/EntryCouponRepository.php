<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon;
use DoubleThreeDigital\SimpleCommerce\Contracts\CouponRepository as RepositoryContract;
use DoubleThreeDigital\SimpleCommerce\Exceptions\CouponNotFound;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Illuminate\Support\Arr;
use Statamic\Facades\Entry;
use Statamic\Facades\Stache;

class EntryCouponRepository implements RepositoryContract
{
    protected $collection;

    public function __construct()
    {
        $this->collection = SimpleCommerce::couponDriver()['collection'];
    }

    public function all()
    {
        return Entry::whereCollection($this->collection)->all();
    }

    public function find($id): ?Coupon
    {
        $entry = Entry::find($id);

        if (! $entry) {
            throw new CouponNotFound("Coupon [{$id}] could not be found.");
        }

        return app(Coupon::class)
            ->resource($entry)
            ->id($entry->id())
            ->code($entry->slug())
            ->type($entry->get('type'))
            ->value($entry->get('coupon_value') ?? $entry->get('value')) // TODO 4.0: Only coupon_value should be supported
            ->data(array_merge(
                $entry->data()->except(['coupon_value', 'value', 'type'])->toArray(),
                [
                    'site' => optional($entry->site())->handle(),
                    'slug' => $entry->slug(),
                    'published' => $entry->published(),
                ]
            ));
    }

    public function findByCode(string $code): ?Coupon
    {
        $entry = Entry::query()
            ->where('collection', $this->collection)
            ->where('slug', $code)
            ->first();

        if (! $entry) {
            throw new CouponNotFound("Coupon [{$code}] could not be found.");
        }

        return $this->find($entry->id());
    }

    public function make(): Coupon
    {
        return app(Coupon::class);
    }

    public function save(Coupon $coupon): void
    {
        $entry = $coupon->resource();

        if (! $entry) {
            $entry = Entry::make()
                ->id(Stache::generateId())
                ->collection($this->collection);
        }

        if ($coupon->get('site')) {
            $entry->site($coupon->get('site'));
        }

        $entry->slug($coupon->code());

        if ($coupon->get('published')) {
            $entry->published($coupon->get('published'));
        }

        $entry->data(
            array_merge(
                Arr::except($coupon->data()->toArray(), ['id', 'site', 'slug', 'published']),
                [
                    'type' => $coupon->type(),
                    'coupon_value' => $coupon->value(),
                ]
            )
        );

        $entry->save();

        $coupon->id = $entry->id();
        $coupon->code = $entry->slug();
        $coupon->type = $entry->get('type');
        $coupon->value = $entry->get('coupon_value');
        $coupon->resource = $entry;

        $coupon->merge([
            'site' => $entry->site()->handle(),
            'slug' => $entry->slug(),
            'published' => $entry->published(),
        ]);
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->resource()->delete();
    }

    protected function isUsingEloquentDriverWithIncrementingIds(): bool
    {
        return config('statamic.eloquent-driver.entries.model') === \Statamic\Eloquent\Entries\EntryModel::class;
    }

    public static function bindings(): array
    {
        return [];
    }
}
