<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\ProductRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
use Illuminate\Support\Arr;
use Statamic\CP\Breadcrumbs;
use Statamic\Http\Controllers\CP\CpController;

class ProductController extends CpController
{
    public function index()
    {
        $this->authorize('view', Product::class);

        return view('simple-commerce::cp.products.index', [
            'products' => Product::paginate(config('statamic.cp.pagination_size')),
            'createUrl' => (new Product())->createUrl(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Products', 'url' => cp_route('products.index')]]);

        $blueprint = (new Product())->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.products.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
            'action'    => cp_route('products.store'),
        ]);
    }

    public function store(ProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $product = Product::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'product_category_id' => $request->category[0] ?? null,
            'is_enabled' => $request->is_enabled,
        ]);

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
            ->each(function ($theVariant) use ($product, $request) {
                $variant = $product->variants()->create([
                    'name' => $theVariant['name'],
                    'sku' => $theVariant['sku'],
                    'price' => $theVariant['price'],
                    'stock' => $theVariant['stock_number'],
                    'unlimited_stock' => $theVariant['unlimited_stock'],
                    'max_quantity' => $theVariant['max_quantity'],
                    'description' => $theVariant['description'],
                ]);

                collect($theVariant)
                    ->each(function ($value, $key) use ($variant) {
                        if (str_contains($key, 'attributes_')) {
                            $attributeKey = str_replace('attributes_', '', $key);
                            $attributeValue = $value;

                            $variant->attributes()->create([
                                'key'   => $attributeKey,
                                'value' => $attributeValue,
                            ]);
                        }
                    });
            });

        return [
            'redirect' => cp_route('products.edit', [
                'product' => $product->uuid,
            ]),
        ];
    }

    public function edit($product)
    {
        $this->authorize('update', $product);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Products', 'url' => cp_route('products.index')]]);

        $product = Product::with('variants', 'attributes')
            ->where('uuid', $product)
            ->first();

        $values = array_merge($product->toArray(), [
            'category'  => $product->product_category_id,
            'variants'  => $product->variants->map(function (Variant $originalVariant, $key) {
                $variant = $originalVariant->toArray();

                $originalVariant->attributes->each(function (Attribute $attribute) use (&$variant) {
                    $variant["attributes_{$attribute->key}"] = $attribute['value'];
                });

                return array_merge($variant, [
                    '_id'           => "row-{$key}",
                    'stock_number'  => $variant['stock'], // TODO: need to change the blueprint field handle to be the same as the db column name
                ]);
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

        $blueprint = (new Product())->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.products.edit', [
            'crumbs'    => $crumbs,
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $values,
            'meta'      => $fields->meta(),
            'action'    => $product->updateUrl(),
        ]);
    }

    public function update(ProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $product->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'description' => $request->description,
            'product_category_id' => $request->category,
            'is_enabled' => $request->is_enabled,
        ]);

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

                $requestAttributes = collect($variant)
                    ->reject(function ($value, $key) {
                        if (str_contains($key, 'attributes')) {
                            return false;
                        }

                        return true;
                    })
                    ->map(function ($value, $key) use ($item) {
                        $key = str_replace('attributes_', '', $key);

                        $attribute = $item->attributes()->updateOrCreate([
                            'key' => $key,
                        ], [
                            'key' => $key,
                            'value' => $value,
                        ]);

                        return $attribute;
                    });

                return $variant;
            });

        $product->variants
            ->filter(function ($variant) use ($requestVariants) {
                return ! $requestVariants
                    ->contains('uuid', $variant->uuid);
            })
            ->each
            ->delete();

        return $product;
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->variants()->delete();
        $product->delete();

        return back()
            ->with('success', "$product->title has been deleted.");
    }
}
