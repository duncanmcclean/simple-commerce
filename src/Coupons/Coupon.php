<?php

namespace DoubleThreeDigital\SimpleCommerce\Coupons;

use Carbon\Carbon;
use DoubleThreeDigital\SimpleCommerce\Contracts\Coupon as Contract;
use DoubleThreeDigital\SimpleCommerce\Contracts\Order;
use DoubleThreeDigital\SimpleCommerce\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Coupon as CouponFacade;
use DoubleThreeDigital\SimpleCommerce\Facades\Order as OrderFacade;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Coupon implements Contract
{
    use FluentlyGetsAndSets, ExistsAsFile, TracksQueriedColumns, ContainsData;

    public $id;

    public $code;

    public $value;

    public $type;

    public $enabled;

    protected $selectedQueryRelations = [];

    public function __construct()
    {
        $this->data = collect();
        $this->enabled = true;
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function code($id = null)
    {
        return $this
            ->fluentlyGetOrSet('code')
            ->args(func_get_args());
    }

    public function type($type = null)
    {
        return $this
            ->fluentlyGetOrSet('type')
            ->getter(function ($type) {
                return CouponType::from($type);
            })
            ->setter(function ($value) {
                if ($value === 'fixed') {
                    $value = CouponType::Fixed;
                }

                if ($value === 'percentage') {
                    $value = CouponType::Percentage;
                }

                // If the value is already set, it's over 100 and it's a percentage, we want to divide it by 100...
                if ($value === CouponType::Percentage && $this->value > 100) {
                    $this->value = $this->value / 100;
                }

                return $value?->value;
            })
            ->args(func_get_args());
    }

    public function value($value = null)
    {
        return $this
            ->fluentlyGetOrSet('value')
            ->setter(function ($value) {
                if (is_string($value) && str_contains($value, '.')) {
                    $value = (int) str_replace('.', '', $value);
                }

                if ($this->type === CouponType::Percentage && $value > 100) {
                    return $value / 100;
                }

                return $value;
            })
            ->args(func_get_args());
    }

    public function enabled($enabled = null)
    {
        return $this
            ->fluentlyGetOrSet('enabled')
            ->args(func_get_args());
    }

    public function isValid(Order $order): bool
    {
        $order = OrderFacade::find($order->id());

        if (! $this->enabled()) {
            return false;
        }

        if ($this->has('expires_at') && $this->get('expires_at') !== null) {
            if (Carbon::parse($this->get('expires_at'))->isPast()) {
                return false;
            }
        }

        if ($this->has('minimum_cart_value') && $order->itemsTotal()) {
            if ($order->itemsTotal() < $this->get('minimum_cart_value')) {
                return false;
            }
        }

        if ($this->has('redeemed') && $this->has('maximum_uses') && $this->get('maximum_uses') !== null) {
            if ($this->get('redeemed') >= $this->get('maximum_uses')) {
                return false;
            }
        }

        if ($this->isProductSpecific()) {
            $couponProductsInOrder = $order->lineItems()->filter(function ($lineItem) {
                return in_array($lineItem->product()->id(), $this->get('products'));
            });

            if ($couponProductsInOrder->count() === 0) {
                return false;
            }
        }

        if ($this->isCustomerSpecific()) {
            $isCustomerAllowed = collect($this->get('customers'))
                ->contains(optional($order->customer())->id());

            if (! $isCustomerAllowed) {
                return false;
            }
        }

        return true;
    }

    public function redeem(): self
    {
        $redeemed = $this->has('redeemed') ? $this->get('redeemed') : 0;

        $this->set('redeemed', $redeemed + 1);
        $this->save();

        return $this;
    }

    protected function isProductSpecific()
    {
        return $this->has('products')
            && collect($this->get('products'))->count() >= 1;
    }

    protected function isCustomerSpecific()
    {
        return $this->has('customers')
            && collect($this->get('customers'))->count() >= 1;
    }

    public function beforeSaved()
    {
        return null;
    }

    public function afterSaved()
    {
        return null;
    }

    public function save(): self
    {
        if (! $this->id) {
            $this->id = app('stache')->generateId();
        }

        if (method_exists($this, 'beforeSaved')) {
            $this->beforeSaved();
        }

        CouponFacade::save($this);

        if (method_exists($this, 'afterSaved')) {
            $this->afterSaved();
        }

        return $this;
    }

    public function delete(): void
    {
        CouponFacade::delete($this);
    }

    public function fresh(): self
    {
        $freshCoupon = CouponFacade::find($this->id());

        $this->id = $freshCoupon->id;
        $this->code = $freshCoupon->code;
        $this->value = $freshCoupon->value;
        $this->type = $freshCoupon->type;
        $this->enabled = $freshCoupon->enabled;
        $this->data = $freshCoupon->data();

        return $this;
    }

    public function toArray(): array
    {
        return array_merge($this->data()->toArray(), [
            'id' => $this->id,
            'code' => $this->code,
            'value' => $this->value,
            'type' => $this->type,
            'enabled' => $this->enabled,
        ]);
    }

    public function toResource()
    {
        return $this->toArray();
    }

    public function toAugmentedArray($keys = null)
    {
        $blueprint = CouponBlueprint::getBlueprint();

        return $blueprint->fields()->addValues($this->toArray())->all()->map->augment($keys)->mapWithKeys(function ($field) {
            return [$field->handle() => $field->value()];
        })->all();
    }

    public function editUrl()
    {
        return cp_route('simple-commerce.coupons.edit', [
            'coupon' => $this->id(),
        ]);
    }

    public function updateUrl()
    {
        return cp_route('simple-commerce.coupons.update', [
            'coupon' => $this->id(),
        ]);
    }

    public function deleteUrl()
    {
        return cp_route('simple-commerce.coupons.destroy', [
            'coupon' => $this->id(),
        ]);
    }

    public function discountText(): string
    {
        if ($this->type() === CouponType::Percentage) {
            return "{$this->value()}%";
        }

        if ($this->type() === CouponType::Fixed) {
            return Currency::parse($this->value(), Site::current());
        }

        return null;
    }

    public function path()
    {
        return Stache::store('simple-commerce-coupons')->directory().str_slug($this->code()).'.yaml';
    }

    public function fileData()
    {
        return $this->toArray();
    }

    public function selectedQueryRelations($relations)
    {
        $this->selectedQueryRelations = $relations;

        return $this;
    }
}
