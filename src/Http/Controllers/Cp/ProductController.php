<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Http\Requests\ProductStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductUpdateRequest;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\Variant;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class ProductController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
        ]);

        $products = Product::all();

        return view('commerce::cp.products.index', [
            'crumbs' => $crumbs,
            'products' => $products,
            'createUrl' => (new Product())->createUrl(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

        $blueprint = Blueprint::find('product');
        $fields = $blueprint->fields();
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
        $this->authorize('create', Product::class);

        $validation = $request->validated();

        $product = new Product();
        $product->uid = (new Stache())->generateId();
        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->product_category_id = $request->category[0] ?? null;
        $product->is_enabled = true;
        $product->save();

        collect($request->variants)
            ->each(function ($variant) use ($product) {
                $item = new Variant();
                $item->uid = (new Stache())->generateId();
                $item->name = $variant['name'];
                $item->sku = $variant['sku'];
                $item->price = $variant['price'];
                $item->stock = $variant['stock_number'];
                $item->unlimited_stock = $variant['unlimited_stock'];
                $item->max_quantity = $variant['max_quantity'];
                $item->description = $variant['description'];
                $item->variant_attributes = $variant['attributes'];
                $item->product_id = $product->id;
                $item->save();
            });

        return ['redirect' => cp_route('products.edit', ['product' => $product->uid])];
    }

    public function edit($product)
    {
        $this->authorize('update', $product);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

        $product = Product::where('uid', $product)
            ->with('variants')
            ->first();

        $blueprint = Blueprint::find('product');

        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('commerce::cp.products.edit', [
            'crumbs'    => $crumbs,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $product,
            'meta'      => $fields->meta(),
            'action' => $product->updateUrl(),
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product)
    {
        $this->authorize('update', $product);

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
        $this->authorize('delete', $product);

        $product->delete();

        return redirect(cp_route('products.index'));
    }
}
