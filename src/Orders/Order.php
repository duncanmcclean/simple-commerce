<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use ArrayAccess;
use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;
use DuncanMcClean\SimpleCommerce\Contracts\Coupons\Coupon;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as Contract;
use DuncanMcClean\SimpleCommerce\Customers\GuestCustomer;
use DuncanMcClean\SimpleCommerce\Data\HasAddresses;
use DuncanMcClean\SimpleCommerce\Events\OrderCreated;
use DuncanMcClean\SimpleCommerce\Events\OrderSaved;
use DuncanMcClean\SimpleCommerce\Events\OrderStatusUpdated;
use DuncanMcClean\SimpleCommerce\Facades\Coupon as CouponFacade;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderFacade;
use DuncanMcClean\SimpleCommerce\Payments\Gateways\PaymentGateway;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingMethod;
use DuncanMcClean\SimpleCommerce\Shipping\ShippingOption;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
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
use Statamic\Facades\Site;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use DuncanMcClean\SimpleCommerce\Facades;

class Order implements Arrayable, ArrayAccess, Augmentable, ContainsQueryableValues, Contract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAddresses, HasAugmentedInstance, HasDirtyState, HasTotals, TracksQueriedColumns, TracksQueriedRelations;

    protected $id;
    protected $orderNumber;
    protected $date;
    protected $cart;
    protected $status;
    protected $customer;
    protected $coupon;
    protected $lineItems;
    protected $site;
    protected $initialPath;

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
        $this->lineItems = new LineItems;
        $this->status = OrderStatus::PaymentPending->value;
    }

    public function id($id = null)
    {
        return $this
            ->fluentlyGetOrSet('id')
            ->args(func_get_args());
    }

    public function orderNumber($orderNumber = null)
    {
        return $this
            ->fluentlyGetOrSet('orderNumber')
            ->args(func_get_args());
    }

    public function date($date = null)
    {
        return $this
            ->fluentlyGetOrSet('date')
            ->setter(function ($date) {
                if ($date === null) {
                    return null;
                }

                if ($date instanceof \Carbon\CarbonInterface) {
                    return $date->setTimezone('UTC');
                }

                if (strlen($date) === 10) {
                    return Carbon::createFromFormat('Y-m-d', $date)
                        ->setTimezone('UTC')
                        ->startOfDay();
                }

                if (strlen($date) === 15) {
                    return Carbon::createFromFormat('Y-m-d-Hi', $date)
                        ->setTimezone('UTC')
                        ->startOfMinute();
                }

                return Carbon::createFromFormat('Y-m-d-His', $date)
                    ->setTimezone('UTC');
            })
            ->args(func_get_args());
    }

    public function cart($cart = null)
    {
        return $this
            ->fluentlyGetOrSet('cart')
            ->setter(function ($cart) {
                if ($cart instanceof Cart) {
                    return $cart->id();
                }

                return $cart;
            })
            ->args(func_get_args());
    }

    public function status($status = null)
    {
        return $this
            ->fluentlyGetOrSet('status')
            ->getter(function ($status) {
                if (! $status) {
                    return OrderStatus::PaymentPending;
                }

                return OrderStatus::from($status);
            })
            ->setter(function ($status) {
                if ($status instanceof OrderStatus) {
                    return $status->value;
                }

                return $status;
            })
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

    public function lineItems($lineItems = null)
    {
        return $this
            ->fluentlyGetOrSet('lineItems')
            ->setter(function ($lineItems) {
                // When we're creating an order from a cart, let's allow the actual LineItems
                // instance to be passed instead of casting to/from an array.
                if ($lineItems instanceof LineItems) {
                    return $lineItems;
                }

                $items = new LineItems;

                collect($lineItems)->each(fn (array $lineItem) => $items->create($lineItem));

                return $items;
            })
            ->args(func_get_args());
    }

    public function shippingMethod(): ?ShippingMethod
    {
        if (! $this->get('shipping_method')) {
            return null;
        }

        return Facades\ShippingMethod::find($this->get('shipping_method'));
    }

    public function shippingOption(): ?ShippingOption
    {
        if (! $this->shippingMethod() || ! $this->get('shipping_option')) {
            return null;
        }

        return ShippingOption::make($this->shippingMethod())
            ->name(Arr::get($this->get('shipping_option'), 'name'))
            ->handle(Arr::get($this->get('shipping_option'), 'handle'))
            ->price(Arr::get($this->get('shipping_option'), 'price'));
    }

    public function paymentGateway(): ?PaymentGateway
    {
        if (! $this->get('payment_gateway')) {
            return null;
        }

        return Facades\PaymentGateway::find($this->get('payment_gateway'));
    }

    public function site($site = null)
    {
        return $this
            ->fluentlyGetOrSet('site')
            ->setter(function ($site) {
                return $site instanceof \Statamic\Sites\Site ? $site->handle() : $site;
            })
            ->getter(function ($site) {
                if (! $site) {
                    return Site::default();
                }

                if ($site instanceof \Statamic\Sites\Site) {
                    return $site;
                }

                return Site::get($site);
            })
            ->args(func_get_args());
    }

    public function save(): bool
    {
        $isNew = is_null(OrderFacade::find($this->id()));

        OrderFacade::save($this);

        if ($isNew) {
            event(new OrderCreated($this));
        }

        event(new OrderSaved($this));

        if ($this->isDirty('status') && $this->getOriginal('status')) {
            event(new OrderStatusUpdated(
                order: $this,
                oldStatus: OrderStatus::from($this->getOriginal('status')),
                newStatus: $this->status())
            );
        }

        return true;
    }

    public function delete(): bool
    {
        OrderFacade::delete($this);

        return true;
    }

    public function path(): string
    {
        return $this->initialPath ?? $this->buildPath();
    }

    public function buildPath(): string
    {
        return vsprintf('%s/%s%s.%s.yaml', [
            rtrim(Stache::store('orders')->directory(), '/'),
            Site::multiEnabled() ? $this->site()->handle().'/' : '',
            $this->date()->format('Y-m-d-His'),
            $this->orderNumber(),
        ]);
    }

    public function fileData(): array
    {
        return $this->data()->merge([
            'id' => $this->id(),
            'cart' => $this->cart(),
            'status' => $this->status()->value,
            'customer' => $this->customer,
            'coupon' => $this->coupon,
            'line_items' => $this->lineItems()->map->fileData()->all(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ])->filter()->all();
    }

    public function fresh(): ?Order
    {
        return OrderFacade::find($this->id());
    }

    public function blueprint(): StatamicBlueprint
    {
        return OrderFacade::blueprint();
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'order_number', 'date', 'status', 'grand_total', 'sub_total', 'discount_total', 'tax_total', 'shipping_total'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedOrder($this);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'order_number' => $this->orderNumber(),
            'date' => $this->date(),
            'cart' => $this->cart(),
            'status' => $this->status()?->value,
            'customer' => $this->customer(),
            'coupon' => $this->coupon(),
            'line_items' => $this->lineItems(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ], $this->data()->toArray());
    }

    public function editUrl(): string
    {
        return cp_route('simple-commerce.orders.edit', $this->id());
    }

    public function updateUrl(): string
    {
        return cp_route('simple-commerce.orders.update', $this->id());
    }

    public function reference(): string
    {
        return "order::{$this->id()}";
    }

    public function getQueryableValue(string $field)
    {
        if ($field === 'status') {
            return $this->status()->value;
        }

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
