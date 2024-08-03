<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use ArrayAccess;
use DuncanMcClean\SimpleCommerce\Contracts\Orders\Order as Contract;
use DuncanMcClean\SimpleCommerce\Facades\Order as OrderFacade;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Order implements Arrayable, ArrayAccess, Augmentable, Contract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, TracksQueriedColumns, TracksQueriedRelations, HasDirtyState, Calculable;

    protected $id;
    protected $orderNumber;
    protected $cart;
    protected $customer;
    protected $lineItems;
    protected $initialPath;

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

    public function orderNumber($orderNumber = null)
    {
        return $this
            ->fluentlyGetOrSet('orderNumber')
            ->args(func_get_args());
    }

    public function cart($cart = null)
    {
        return $this
            ->fluentlyGetOrSet('cart')
            ->setter(function ($cart) {
                if ($cart instanceof \DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart) {
                    return $cart->id();
                }

                return $cart;
            })
            ->args(func_get_args());
    }

    public function status(): OrderStatus
    {
        // TODO: When order has payment gateway data, but the payment is not completed, return OrderStatus::PendingPayment
        // TODO: When order has payment gateway data, and the payment is completed, return OrderStatus::Completed

        if ($this->get('is_cancelled')) {
            return OrderStatus::Cancelled;
        }

        return OrderStatus::Pending;
    }

    public function customer($customer = null)
    {
        // todo: refactor to support user ID or array of customer data
        return $this
            ->fluentlyGetOrSet('customer')
//            ->setter(function ($customer) {
//                if ($customer instanceof CustomerContract) {
//                    return $customer;
//                }
//
//                return Customer::find($customer);
//            })
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

    public function shippingMethod($shippingMethod = null)
    {
        return $this
            ->fluentlyGetOrSet('shippingMethod')
            ->args(func_get_args());
    }

    public function paymentGateway($paymentGateway = null)
    {
        return $this
            ->fluentlyGetOrSet('paymentGateway')
            ->args(func_get_args());
    }

    public function paymentData($paymentData = null)
    {
        return $this
            ->fluentlyGetOrSet('paymentData')
            ->args(func_get_args());
    }

    public function save(): bool
    {
        OrderFacade::save($this);

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
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('orders')->directory(), '/'),
            $this->orderNumber() ?? $this->id(),
        ]);
    }

    public function fileData(): array
    {
        return array_merge([
            'id' => $this->id(),
            'cart' => $this->cart(),
            'customer' => $this->customer(),
            'line_items' => $this->lineItems()->map->toArray()->all(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
        ], $this->data->all());
    }

    public function fresh(): Order
    {
        return OrderFacade::find($this->id());
    }

    public function blueprint(): \Statamic\Fields\Blueprint
    {
        return Blueprint::getBlueprint();
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'order_number', 'status', 'grand_total', 'sub_total', 'discount_total', 'tax_total', 'shipping_total'];
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedOrder($this);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'order_number' => $this->orderNumber(),
            'customer' => $this->customer(),
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
}
