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
            ->entry($entry)
            ->id($entry->id())
            ->code($entry->slug())
            ->data(array_merge(
                $entry->data()->toArray(),
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

    public function create(array $data = [], string $site = ''): Coupon
    {
        if (! $this->isUsingEloquentDriverWithIncrementingIds()) {
            $id = Stache::generateId();
        }

        $coupon = app(Coupon::class)
            ->id($id)
            ->code(isset($data['code']) ? $data['code'] : $data['slug'])
            ->data($data);

        $coupon->save();

        return $coupon;
    }

    public function save($coupon): void
    {
        $entry = $coupon->entry();

        if (! $entry) {
            $entry = Entry::make()
                ->id($coupon->id())
                ->collection($this->collection);
        }

        if ($coupon->get('site')) {
            $entry->site($coupon->get('site'));
        }

        // if ($coupon->get('slug')) {
        //     $entry->slug($coupon->get('slug'));
        // }

        $entry->slug($coupon->code());

        if ($coupon->get('published')) {
            $entry->published($coupon->get('published'));
        }

        $entry->data(
            Arr::except($coupon->data(), ['id', 'site', 'slug', 'published'])
        );

        $entry->save();
    }

    public function delete($coupon): void
    {
        $coupon->entry()->delete();
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
