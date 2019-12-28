<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Product;
use Damcclean\Commerce\Http\Requests\ProductStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductUpdateRequest;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class ProductController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
        ]);

        $products = Product::all()
            ->map(function ($product) {
                return array_merge($product->toArray(), [
                    'price' => config('commerce.currency.symbol').$product['price'],
                    'edit_url' => cp_route('products.edit', ['product' => $product['id']]),
                    'delete_url' => cp_route('products.destroy', ['product' => $product['id']]),
                ]);
            });

        return view('commerce::cp.products.index', [
            'products' => $products,
            'crumbs' => $crumbs,
        ]);
    }

    public function create()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

        $blueprint = Blueprint::find('product');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.products.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function store(ProductStoreRequest $request)
    {
        $validation = $request->validated();

        $product = Product::save($request->all());

        return ['redirect' => cp_route('products.edit', ['product' => $product->data['id']])];
    }

    public function edit($product)
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

        $product = Product::find($product);

        $blueprint = Blueprint::find('product');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.products.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $product,
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function update(ProductUpdateRequest $request, $product)
    {
        $validation = $request->validated();

        return Product::update($product, $request->all());
    }

    public function destroy($product)
    {
        $product = Product::delete(Product::find($product)['slug']);

        return redirect(cp_route('products.index'));
    }
}
