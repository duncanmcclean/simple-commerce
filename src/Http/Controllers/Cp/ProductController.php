<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Facades\Product;
use Damcclean\Commerce\Http\Requests\ProductStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductUpdateRequest;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class ProductController extends CpController
{
    public function index()
    {
        return view('commerce::cp.products.index', [
            'products' => Product::all(),
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

        $product = Product::save($request->all());

        return ['redirect' => cp_route('products.edit', ['product' => $product->data['id']])];
    }

    public function edit($product)
    {
        $product = Product::find($product);

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

        return Product::update(Product::find($product)->toArray()['id'], $request->all());
    }

    public function destroy($product)
    {
        $product = Product::delete(Product::find($product)['slug']);

        return redirect(cp_route('products.index'));
    }
}
