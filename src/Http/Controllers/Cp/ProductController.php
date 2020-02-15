<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency;
use DoubleThreeDigital\SimpleCommerce\Http\Requests\ProductRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;

class ProductController extends CpController
{
    public function index()
    {
        $this->authorize('view', Product::class);

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

    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = new Product();
        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->product_category_id = $request->category[0] ?? null;
        $product->is_enabled = true;
        $product->save();

        collect($request->product_attributes)
            ->each(function ($attribute) use ($product) {
                if ($attribute['key'] === null) {
                    return;
                }

                $product->attributes()->create([
                    'key' => $attribute['key'],
                    'value' => $attribute['value'],
                ]);
            });

        collect($request->variants)
            ->each(function ($variant) use ($product, $request) {
                $item = new Variant();
                $item->name = $variant['name'];
                $item->sku = $variant['sku'];
                $item->price = $variant['price'];
                $item->stock = $variant['stock_number'];
                $item->unlimited_stock = $variant['unlimited_stock'];
                $item->max_quantity = $variant['max_quantity'];
                $item->description = $variant['description'];
                $item->product_id = $product->id;
                $item->save();

                collect($variant['variant_attributes'])
                    ->each(function ($attribute) use ($item) {
                        if ($attribute['key'] === null) {
                            return;
                        }

                        $item->attributes()->create([
                            'key' => $attribute['key'],
                            'value' => $attribute['value'],
                        ]);
                    });
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
                    'variant_attributes' => collect($variant->attributes)
                        ->map(function (Attribute $attribute, $key) {
                            return [
                                '_id' => 'row-'.$key,
                                'uuid' => $attribute->uuid,
                                'key' => $attribute->key,
                                'value' => $attribute->value,
                            ];
                        })
                        ->toArray(),
                ];
            }),
            'product_attributes' => collect($product->attributes)
                ->map(function (Attribute $attribute, $key) {
                    return [
                        '_id' => 'row-'.$key,
                        'uuid' => $attribute->uuid,
                        'key' => $attribute->key,
                        'value' => $attribute->value,
                    ];
                })
                ->toArray(),
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

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product->title = $request->title;
        $product->slug = $request->slug;
        $product->description = $request->description;
        $product->product_category_id = $request->category;
        $product->is_enabled = true;
        $product->save();

        $requestVariants = collect($request->variants)
            ->map(function ($variant) use ($product) {
                $item = Variant::updateOrCreate([
                    'uuid' => $variant['uuid'] ?? null,
                ], [
                    'name' => $variant['name'],
                    'sku' => $variant['sku'],
                    'price' => $variant['price'],
                    'stock' => $variant['stock_number'],
                    'unlimited_stock' => $variant['unlimited_stock'],
                    'max_quantity' => $variant['max_quantity'],
                    'description' => $variant['description'],
                    'product_id' => $product->id,
                ]);

                $requestAttributes = collect($variant['variant_attributes'])
                    ->map(function ($attribute) use ($item) {
                        if ($attribute['key'] === null) {
                            return $attribute;
                        }

                        $attributeRecord = $item->attributes()->updateOrCreate([
                            'uuid' => $attribute['uuid'],
                        ], [
                            'key' => $attribute['key'],
                            'value' => $attribute['value'],
                        ]);

                        $attribute['uuid'] = $attributeRecord->uuid;

                        return $attribute;
                    });

                $item->attributes
                    ->filter(function ($attribute) use ($requestAttributes) {
                        return ! $requestAttributes
                            ->contains(function ($requestAttribute) use ($attribute) {
                                return $attribute->uuid === $requestAttribute['uuid'];
                            });
                    })
                    ->each->delete();

                $variant['uuid'] = $item->uuid;

                return $variant;
            });

        $product->variants
            ->filter(function ($variant) use ($requestVariants) {
                return ! $requestVariants
                    ->contains('uuid', $variant->uuid);
            })
            ->each->delete();

        return $product;
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        collect($product->variants)
            ->each(function (Variant $variant) {
                $variant->attributes()->delete();

                $variant->delete();
            });

        $product->attributes()->delete();

        $product->delete();

        return back()
            ->with('success', "$product->title has been deleted.");
    }
}
