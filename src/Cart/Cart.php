<?php

namespace DuncanMcClean\SimpleCommerce\Cart;

use ArrayAccess;
use DuncanMcClean\SimpleCommerce\Facades\Cart as CartFacade;
use DuncanMcClean\SimpleCommerce\Orders\AugmentedOrder;
use DuncanMcClean\SimpleCommerce\Orders\Blueprint;
use DuncanMcClean\SimpleCommerce\Orders\Calculable;
use DuncanMcClean\SimpleCommerce\Orders\LineItems;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Data\Augmentable;
use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart as Contract;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Facades\Stache;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class Cart implements Arrayable, ArrayAccess, Augmentable, Contract
{
    use ContainsData, ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance, TracksQueriedColumns, TracksQueriedRelations, HasDirtyState, Calculable;

    protected $id;
    protected $customer;
    protected $lineItems;
    protected $shippingMethod;
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

    public function save(): bool
    {
        CartFacade::save($this);

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
            'customer' => $this->customer(),
            'line_items' => $this->lineItems()->map->toArray()->all(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
            'shipping_method' => $this->shippingMethod(),
        ], $this->data->all());
    }

    public function fresh(): Cart
    {
        return CartFacade::find($this->id());
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
            'line_items' => $this->lineItems(),
            'grand_total' => $this->grandTotal(),
            'sub_total' => $this->subTotal(),
            'discount_total' => $this->discountTotal(),
            'tax_total' => $this->taxTotal(),
            'shipping_total' => $this->shippingTotal(),
            'shipping_method' => $this->shippingMethod(),
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
}
