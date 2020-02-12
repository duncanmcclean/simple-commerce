<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ProductStoreRequest;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ProductUpdateRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class ProductController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
        ]);

        $products = Product::paginate(config('statamic.cp.pagination_size'));

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
            ['text' => 'Simple Commerce'],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

        $blueprint = Blueprint::find('simple-commerce/product');
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
        $product->uuid = (new Stache())->generateId();
        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->product_category_id = $request->category[0] ?? null;
        $product->is_enabled = true;
        $product->save();

        collect($request->variants)
            ->each(function ($variant) use ($product) {
                $item = new Variant();
                $item->uuid = (new Stache())->generateId();
                $item->name = $variant['name'];
                $item->sku = $variant['sku'];
                $item->price = $variant['price'];
                $item->stock = $variant['stock_number'];
                $item->unlimited_stock = $variant['unlimited_stock'];
                $item->max_quantity = $variant['max_quantity'];
                $item->description = $variant['description'];
                $item->variant_attributes = $variant['variant_attributes'];
                $item->product_id = $product->id;
                $item->save();
            });

        return ['redirect' => cp_route('products.edit', ['product' => $product->uuid])];
    }

    public function edit($product)
    {
        $this->authorize('update', $product);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Simple Commerce'],
            ['text' => 'Products', 'url' => cp_route('products.index')],
        ]);

        $product = Product::where('uuid', $product)->first();
        $variants = Variant::where('product_id', $product->id)->get();

        $values = array_merge($product->toArray(), [
            'category' => $product->product_category_id,
            'variants' => $variants->map(function (Variant $variant, $key) {
                return [
                    '_id' => 'row-'.$key,
                    'uuid' => $variant->uuid,
                    'description' => $variant->description,
                    'max_quantity' => $variant->max_quantity,
                    'name' => $variant->name,
                    'price' => (new Currency())->parse($variant->price, true, false),
                    'sku' => $variant->sku,
                    'stock_number' => $variant->stock,
                    'unlimited_stock' => $variant->unlimited_stock,
                    'variant_attributes' => collect($variant->variant_attributes)
                        ->map(function ($attribute, $key) {
                            return [
                                '_id' => 'row-'.$key,
                                'key' => $attribute['key'],
                                'value' => $attribute['value'],
                            ];
                        })->toArray(),
                ];
            })->toArray(),
        ]);

        $blueprint = Blueprint::find('simple-commerce/product');

        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('commerce::cp.products.edit', [
            'crumbs'    => $crumbs,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $values,
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
        $product->is_enabled = true;
        $product->save();

        collect($request->variants)
            ->each(function ($variant) use ($product) {
                if (isset($variant['uuid'])) {
                    $item = Variant::where('uuid', $variant['uuid'])->firstOrFail();
                } else {
                    $item = new Variant();
                    $item->uuid = (new Stache())->generateId();
                }

                $item->name = $variant['name'];
                $item->sku = $variant['sku'];
                $item->price = $variant['price'];
                $item->stock = $variant['stock_number'];
                $item->unlimited_stock = $variant['unlimited_stock'];
                $item->max_quantity = $variant['max_quantity'];
                $item->description = $variant['description'];
                $item->variant_attributes = $variant['variant_attributes'];
                $item->product_id = $product->id;
                $item->save();
            });

        return $product;
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        collect($product->variants())
            ->each(function ($variant) {
                $variant->delete();
            });

        $product->delete();

        return redirect(cp_route('products.index'));
    }
}
