<?php

namespace Damcclean\Commerce\Http\Controllers;

use Damcclean\Commerce\Http\Requests\ProductStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductUpdateRequest;
use Facades\Damcclean\Commerce\Models\Product;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class ProductController extends CpController
{
    public function index()
    {
        return view('commerce::cp.products.index', [
            'products' => Product::all()
        ]);
    }

    public function create()
    {
        $blueprint = Blueprint::find('product');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.products.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
        ]);
    }

    public function store(ProductStoreRequest $request)
    {
        $validation = $request->validated();

        $slug = str_slug($request->title);

        $product = Product::save($slug, $request->all());

        return array_merge($product->toArray(), [
            'redirect' => cp_route('products.edit', ['product' => $slug])
        ]);
    }

    public function edit($product)
    {
        $product = Product::get($product);

        $blueprint = Blueprint::find('product');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.products.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $product,
            'meta'      => $fields->meta(),
        ]);
    }

    public function update(ProductUpdateRequest $request, $product)
    {
        $validation = $request->validated();

        $product = Product::update($product, $request->all());

        if ($request->slug != $product) {
            return array_merge($product->toArray(), [
                'redirect' => cp_route('products.edit', ['product' => $request->slug])
            ]);
        }

        return $product->toArray();
    }

    public function destroy($product)
    {
        $product = Product::delete($product);

        return redirect(cp_route('products.index'));
    }
}
