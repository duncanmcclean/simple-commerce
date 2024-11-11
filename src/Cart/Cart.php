<?php

namespace DuncanMcClean\SimpleCommerce\Cart;

use ArrayAccess;
use DuncanMcClean\SimpleCommerce\Cart\Calculator\Calculator;
use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as Contract;
use DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon;
use DuncanMcClean\SimpleCommerce\Contracts\Shipping\ShippingMethod as ShippingMethodContract;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Events\CartSaved;
use DuncanMcClean\SimpleCommerce\Exceptions\CartHasBeenConvertedToOrderException;
use DuncanMcClean\SimpleCommerce\Facades\Cart as CartFacade;
use DuncanMcClean\SimpleCommerce\Facades\Coupon as CouponFacade;
use DuncanMcClean\SimpleCommerce\Facades\Order;
use DuncanMcClean\SimpleCommerce\Facades\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Orders\AugmentedOrder;
use DuncanMcClean\SimpleCommerce\Orders\Calculable;
use DuncanMcClean\SimpleCommerce\Orders\LineItems;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Stache;
use Statamic\Facades\User;
use Statamic\Fields\Blueprint as StatamicBlueprint;
use Statamic\Sites\Site;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Cart implements Arrayable, ArrayAccess, Augmentable, ContainsQueryableValues, Contract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, TracksQueriedColumns, TracksQueriedRelations, HasDirtyState, Calculable;

    protected $id;
    protected $customer;
    protected $coupon;
    protected $shippingMethod;
    protected $lineItems;
    protected $initialPath;
    private bool $withoutRecalculating = false;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
        $this->lineItems = new LineItems;
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function customer($customer = null)
    {
        return $this
            ->fluentlyGetOrSet('customer')
            ->getter(function ($customer) {
                if (! $customer) {
                    return null;
                }

                if (is_array($customer)) {
                    return (new GuestCustomer)->data($customer);
                }

                return User::find($customer);
            })
            ->setter(function ($customer) {
                if (! $customer) {
                    return null;
                }

                if ($customer instanceof Authenticatable) {
                    return $customer->getKey();
                }

                if ($customer instanceof GuestCustomer) {
                    return $customer->toArray();
                }

                return $customer;
            })
            ->args(func_get_args());
    }

    public function coupon($coupon = null)
    {
        return $this
            ->fluentlyGetOrSet('coupon')
            ->getter(function ($coupon) {
                if (! $coupon) {
                    return null;
                }

                return CouponFacade::find($coupon);
            })
            ->setter(function ($coupon) {
                if (! $coupon) {
                    return null;
                }

                if ($coupon instanceof Coupon) {
                    return $coupon->id();
                }

                return $coupon;
            })
            ->args(func_get_args());
    }

    public function shippingMethod($shippingMethod = null)
    {
        return $this->fluentlyGetOrSet('shippingMethod')
            ->getter(function ($shippingMethod) {
                if (! $shippingMethod) {
                    return null;
                }

                return ShippingMethod::find($shippingMethod);
            })
            ->setter(function ($shippingMethod) {
                if ($shippingMethod instanceof ShippingMethodContract) {
                    return $shippingMethod->handle();
                }

                return $shippingMethod;
            })
            ->args(func_get_args());
    }

    public function lineItems($lineItems = null)
    {
        return $this
            ->fluentlyGetOrSet('lineItems')
            ->setter(function ($lineItems) {
                $items = new LineItems;

                collect($lineItems)->each(fn (array $lineItem) => $items->create($lineItem));

                return $items;
            })
            ->args(func_get_args());
    }

    // TODO: Change this when we add support for multi-site.
    public function site(): Site
    {
        return \Statamic\Facades\Site::default();
    }

    public function saveWithoutRecalculating(): bool
    {
        $this->withoutRecalculating = true;

        return $this->save();
    }

    protected function shouldRecalculate(): bool
    {
        if ($this->withoutRecalculating) {
            return false;
        }

        return $this->fingerprint() !== $this->get('fingerprint');
    }

    public function save(): bool
    {
        $this->set('updated_at', Carbon::now()->timestamp);

        if ($this->shouldRecalculate()) {
            $this->recalculate();
        }

        CartFacade::save($this);

        event(new CartSaved($this));

        $this->withoutRecalculating = false;

        return true;
    }

    public function delete(): bool
    {
        CartFacade::delete($this);

        return true;
    }

    public function path(): string
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath(): string
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('carts')->directory(), '/'),
            $this->id(),
        ]);
    }

    public function fileData(): array
    {
        return array_merge([
            'id' => $this->id(),
            'customer' => $this->customer,
            'coupon' => $this->coupon,
            'shipping_method' => $this->shippingMethod,
            'line_items' => $this->lineItems()->map->fileData()->all(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ], $this->data->all());
    }

    public function fresh(): ?Cart
    {
        return CartFacade::find($this->id());
    }

    public function blueprint(): StatamicBlueprint
    {
        return Order::blueprint();
    }

    public function updatableFields(): array
    {
        return $this->blueprint()->fields()->all()->map->handle()->except([
            'id', 'line_items', 'discount_total', 'grand_total', 'shipping_total', 'sub_total', 'tax_total',
        ])->all();
    }

    public function recalculate(): void
    {
        app(Calculator::class)->calculate($this);

        $this->set('fingerprint', $this->fingerprint());
    }

    public function fingerprint(): string
    {
        $payload = [
            'date' => Carbon::now()->timestamp,
            'customer' => $this->customer(),
            'coupon' => $this->coupon(),
            'line_items' => $this->lineItems()->map->toArray()->all(),
            'shipping_method' => $this->get('shipping_method'),
        ];

        return sha1(json_encode($payload));
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id',  'grand_total', 'sub_total', 'discount_total', 'tax_total', 'shipping_total'];
    }

    public function newAugmentedInstance(): Augmented
    {
        // TODO: Should this be shared or should this be separate between carts and orders??
        return new AugmentedOrder($this);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'customer' => $this->customer(),
            'coupon' => $this->coupon(),
            'shipping_method' => $this->shippingMethod(),
            'line_items' => $this->lineItems(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ], $this->data()->toArray());
    }

    public function reference(): string
    {
        return "cart::{$this->id()}";
    }

    public function keys()
    {
        return $this->data->keys();
    }

    public function value($key)
    {
        return $this->get($key);
    }

    public function getQueryableValue(string $field)
    {
        if ($field === 'customer') {
            if (is_array($this->customer)) {
                return $this->customer()->id();
            }

            return $this->customer;
        }

        if ($field === 'coupon') {
            return $this->coupon;
        }

        if (method_exists($this, $method = Str::camel($field))) {
            return $this->{$method}();
        }

        $value = $this->get($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }
}
