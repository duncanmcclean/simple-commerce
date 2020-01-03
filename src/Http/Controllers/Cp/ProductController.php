<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Http\Requests\ProductStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductUpdateRequest;
use Damcclean\Commerce\Models\Product;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

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
                    'edit_url' => cp_route('products.edit', ['product' => $product->uid]),
                    'delete_url' => cp_route('products.destroy', ['product' => $product->uid]),
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

        $product = new Product();
        $product->uid = (new Stache())->generateId();
        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->product_category_id = $request->category[0] ?? null;
        $product->is_enabled = $request->is_enabled;
        $product->save();

        return ['redirect' => cp_route('products.edit', ['product' => $product->uid])];
    }

    public function edit(Product $product)
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

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

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $validation = $request->validated();

        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->product_category_id = $request->category;
        $product->is_enabled = $request->is_enabled;
        $product->save();

        return $product;
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect(cp_route('products.index'));
    }
}
