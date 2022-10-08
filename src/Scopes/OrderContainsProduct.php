<?php

namespace DoubleThreeDigital\SimpleCommerce\Scopes;

use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\EloquentOrderRepository;
use DoubleThreeDigital\SimpleCommerce\Orders\EntryOrderRepository;
use DoubleThreeDigital\SimpleCommerce\SimpleCommerce;
use Statamic\Query\Scopes\Filter;

class OrderContainsProduct extends Filter
{
    public static $title = 'Contains Product';

    public function fieldItems()
    {
        return [
            'products' => [
                'type' => 'select',
                'display' => __('Product'),
                'options' => $this->getProducts(),
                'multiple' => true,
            ],
        ];
    }

    protected function getProducts(): array
    {
        return collect(Product::all())->mapWithKeys(function ($product) {
            return [$product->id() => $product->get('title')];
        })->toArray();
    }

    public function apply($query, $values)
    {
        $products = $values['products'];

        // TODO: Refactor query once we have a better way of querying
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            $query
                ->whereIn('items->0->product', $products)
                ->orWhereIn('items->1->product', $products)
                ->orWhereIn('items->2->product', $products)
                ->orWhereIn('items->3->product', $products)
                ->orWhereIn('items->4->product', $products)
                ->orWhereIn('items->5->product', $products)
                ->orWhereIn('items->6->product', $products)
                ->orWhereIn('items->7->product', $products)
                ->orWhereIn('items->8->product', $products)
                ->orWhereIn('items->9->product', $products);
        }

        // TODO: Make this query work when using the Eloquent driver
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
            //
        }

        return $query;
    }

    public function badge($values)
    {
        $products = collect($values['products'])->map(function ($productId) {
            return Product::find($productId)->get('title');
        })->join(', ');

        return "Contains Product: {$products}";
    }

    public function visibleTo($key)
    {
        if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)) {
            return $key === 'entries'
                && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
        }

        // if ($this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EloquentOrderRepository::class)) {
        //     $orderModelClass = SimpleCommerce::orderDriver()['model'];
        //     $runwayResource = \DoubleThreeDigital\Runway\Runway::findResourceByModel(new $orderModelClass);

        //     return $key === "runway_{$runwayResource->handle()}";
        // }

        return false;
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
