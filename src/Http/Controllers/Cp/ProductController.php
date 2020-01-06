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
        dd($request->all());

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

        $values = array_merge($product->toArray(), [
            'variants' => $product->variants->map(function (Variant $variant) {
                return array_merge($variant->toArray(), [
                    '_id' => 'row-'.$variant['id'],
                    'attributes' => $variant['variant_attributes'],
                ]);
            })->toArray()
        ]);

        return view('commerce::cp.products.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $values,
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,

            'editing' => true,
            'actions' => [
                'save' => $product->updateUrl(),
                'publish' => $product->publishUrl(),
                'unpublish' => $product->publishUrl(),
            ],
            'permalink' => $product->absoluteUrl(),
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
