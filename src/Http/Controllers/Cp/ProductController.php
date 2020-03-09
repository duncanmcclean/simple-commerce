<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\ProductRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Attribute;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\Variant;
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

                collect($theVariant['variant_attributes'])
                    ->each(function ($attribute) use ($variant) {
                        if ($attribute['key'] === null) {
                            return;
                        }

                        $variant->attributes()->create([
                            'key' => $attribute['key'],
                            'value' => $attribute['value'],
                        ]);
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
            'category' => $product->product_category_id,
            'variants' => $product->variants->map(function (Variant $variant, $key) {
                return array_merge($variant->toArray(), [
                    '_id' => 'row-'.$key,
                    'stock_number' => $variant->stock,
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
            ->each
            ->delete();

        return $product;
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->attributes()->delete();
        $product->variants()->attributes()->delete();
        $product->variants()->delete();
        $product->delete();

        return back()
            ->with('success', "$product->title has been deleted.");
    }
}
