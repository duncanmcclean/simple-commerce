<?php

namespace DoubleThreeDigital\SimpleCommerce\Orders\Eloquent;

use DoubleThreeDigital\SimpleCommerce\Orders\Eloquent\Order as OrderModel;
use DoubleThreeDigital\SimpleCommerce\Contracts\Calculator as CalculatorContract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order as Contract;
use DoubleThreeDigital\SimpleCommerce\Events\CartCompleted;
use DoubleThreeDigital\SimpleCommerce\Events\CouponRedeemed;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon;
use DoubleThreeDigital\SimpleCommerce\Orders\Address;
use DoubleThreeDigital\SimpleCommerce\Support\Traits\HasData;
use Statamic\Http\Resources\API\EntryResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Statamic\Entries\Entry;

class OrderRepository implements Contract
{
    use HasData;

    public $id;
    public $site;
    public $title;
    public $slug;
    public $data;
    public $published;

    /** @var \Illuminate\Database\Eloquent\Model $model */
    protected $model;

    public function all()
    {
        return OrderModel::all();
    }

    public function query()
    {
        return OrderModel::query();
    }

    public function find($id): self
    {
        $this->model = OrderModel::find($id);

        $this->id = $this->model->id;
        $this->title = $this->model->title;
        $this->data = Arr::except($this->model->setAppends([])->toArray(), ['id', 'site', 'slug', 'published']);

        return $this;
    }

    public function create(array $data = [], string $site = ''): self
    {
        $this->title = Arr::pull($data, 'title');
        $this->slug = Arr::pull($data, 'slug');
        $this->data = Arr::except($data, ['id', 'site', 'slug', 'published']);

        $this->save();

        return $this;
    }

    public function save(): self
    {
        if (! $this->model) {
            $this->model = new OrderModel();
        }

        $data = $this->data;

        if (! is_null($this->title)) $data['title'] = $this->title;
        if (! is_null($this->slug)) $data['slug'] = $this->slug;

        $this->model->forceFill($data);

        $this->model->save();

        $this->id = $this->model->fresh()->id;

        return $this;
    }

    public function delete()
    {
        $this->model->delete();
    }

    public function toResource(): EntryResource
    {
        return new EntryResource(new Entry);
    }

    public function id()
    {
        return $this->id;
    }

    public function title($title = null)
    {
        if ($title !== '') {
            $this->title = $title;

            return $this;
        }

        return $this->title;
    }

    public function slug($slug = null)
    {
        if ($slug !== '') {
            $this->slug = $slug;

            return $this;
        }

        return $this->id;
    }

    public function site($site = null): self
    {
        if (is_null($site)) {
            return $this->site;
        }

        if (! $site instanceof \Statamic\Sites\Site) {
            $site = \Statamic\Facades\Site::get($site);
        }

        $this->site = $site;

        return $this;
    }

    public function fresh(): self
    {
        return $this->find($this->id);
    }

    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
        $this->model->setAttribute($key, $value)->save();

        return $this;
    }

    public function toArray(): array
    {
        return $this->model->toArray();
    }

    public function billingAddress()
    {
        if ($this->has('use_shipping_address_for_billing')) {
            return $this->shippingAddress();
        }

        if (! $this->has('billing_address')) {
            return null;
        }

        return new Address(
            $this->has('billing_name') ? $this->get('billing_name') : null,
            $this->has('billing_adddress') ? $this->get('billing_address') : null,
            $this->has('billing_city') ? $this->get('billing_city') : null,
            $this->has('billing_country') ? $this->get('billing_country') : null,
            $this->has('billing_zip_code') ? $this->get('billing_zip_code') : null,
        );
    }

    public function shippingAddress()
    {
        if (! $this->has('shipping_address')) {
            return null;
        }

        return new Address(
            $this->has('shipping_name') ? $this->get('shipping_name') : null,
            $this->has('shipping_address') ? $this->get('shipping_address') : null,
            $this->has('shipping_city') ? $this->get('shipping_city') : null,
            $this->has('shipping_country') ? $this->get('shipping_country') : null,
            $this->has('shipping_zip_code') ? $this->get('shipping_zip_code') : null,
            $this->has('shipping_note') ? $this->get('shipping_note') : null,
        );
    }

    public function customer(string $customer = '')
    {
        if ($customer !== '') {
            $this->set('user_id', $customer);

            return $this;
        }

        if (! $this->has('user_id')) {
            return null;
        }

        return $this->model->customer;
    }

    public function coupon(string $coupon = '')
    {
        if ($coupon !== '') {
            $this->set('coupon_id', $coupon);

            return $this;
        }

        if (! $this->has('coupon_id')) {
            return null;
        }

        return $this->model->coupon;
    }

    public function redeemCoupon(string $code): bool
    {
        $coupon = Coupon::findByCode($code);

        if ($coupon->isValid($this)) {
            $this->set('coupon_id', $coupon->id());
            event(new CouponRedeemed($coupon->entry()));

            return true;
        }

        return false;
    }

    public function markAsCompleted(): self
    {
        $this->data([
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        $this->save();

        event(new CartCompleted($this));

        // TODO: send customer emails

        return $this;
    }

    public function buildReceipt(): string
    {
        return URL::temporarySignedRoute('statamic.simple-commerce.receipt.show', now()->addHour(), [
            'orderId' => $this->id,
        ]);
    }

    public function calculateTotals(): self
    {
        $calculate = resolve(CalculatorContract::class)->calculate($this);

        $this->clearLineItems();

        collect($calculate['items'])
            ->each(function ($lineItem) {
                $this->addLineItem($lineItem);
            });

        $this->data(Arr::except($calculate, ['items']));

        $this->save();

        return $this;
    }

    public function lineItems(): Collection
    {
        return $this->model->lineItems()->get()->map(function ($lineItem) {
            return $lineItem->toArray();
        });
    }

    public function lineItem($lineItemId): array
    {
        return $this
            ->lineItems()
            ->find($lineItemId)
            ->toArray();
    }

    public function addLineItem($lineItemData): array
    {
        $lineItem = $this->model->lineItems()->create(array_merge(
            Arr::only($lineItemData, ['product', 'variant', 'quantity', 'total']),
            ['metadata' => Arr::except($lineItemData, ['product', 'variant', 'quantity', 'total'])]
        ));

        $this->calculateTotals();

        return $this->lineItem($lineItem->id);
    }

    public function updateLineItem($lineItemId, array $lineItemData): array
    {
        $lineItem = $this->model->lineItems()->find($lineItemId)->update(array_merge(
            Arr::only($lineItemData, ['product', 'variant', 'quantity', 'total']),
            ['metadata' => Arr::except($lineItemData, ['product', 'variant', 'quantity', 'total'])]
        ));

        $this->calculateTotals();

        return $this->lineItem($lineItemId);
    }

    public function removeLineItem($lineItemId): Collection
    {
        $this->model->lineItems()->find($lineItemId)->delete();

        $this->calculateTotals();

        return $this->lineItems();
    }

    public function clearLineItems(): Collection
    {
        $this->model->lineItems()->each(function ($lineItem) {
            $lineItem->delete();
        });

        $this->calculateTotals();

        return $this->lineItems();
    }

    public static function bindings(): array
    {
        return [];
    }
}
