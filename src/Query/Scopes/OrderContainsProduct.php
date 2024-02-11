<?php

namespace DuncanMcClean\SimpleCommerce\Query\Scopes;

use DuncanMcClean\SimpleCommerce\Facades\Product;
use DuncanMcClean\SimpleCommerce\Orders\EntryOrderRepository;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
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

    public function badge($values)
    {
        $products = collect($values['products'])->map(function ($productId) {
            return Product::find($productId)->get('title');
        })->join(', ');

        return __('Contains Product: :products', ['products' => $products]);
    }

    public function visibleTo($key)
    {
        return $this->isOrExtendsClass(SimpleCommerce::orderDriver()['repository'], EntryOrderRepository::class)
            && $key === 'entries'
            && $this->context['collection'] === SimpleCommerce::orderDriver()['collection'];
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
