<?php

namespace DoubleThreeDigital\SimpleCommerce\Http\Controllers\Cp;

use DoubleThreeDigital\SimpleCommerce\Http\Requests\ProductCategoryRequest;
use DoubleThreeDigital\SimpleCommerce\Models\Product;
use DoubleThreeDigital\SimpleCommerce\Models\ProductCategory;
use Statamic\CP\Breadcrumbs;

use Statamic\Http\Controllers\CP\CpController;

class ProductCategoryController extends CpController
{
    public function index()
    {
        $this->authorize('view', ProductCategory::class);

        return view('simple-commerce::cp.product-categories.index', [
            'categories'    => ProductCategory::paginate(config('statamic.cp.pagination_size')),
            'createUrl'     => (new ProductCategory())->createUrl(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', ProductCategory::class);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')]]);

        $blueprint = (new ProductCategory())->blueprint();
        $fields = $blueprint->fields();
        $fields = $fields->preProcess();

        return view('simple-commerce::cp.product-categories.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
            'action'    => cp_route('product-categories.store'),
        ]);
    }

    public function store(ProductCategoryRequest $request)
    {
        $this->authorize('create', ProductCategory::class);

        $category = ProductCategory::create([
            'title' => $request->title,
            'slug' => $request->slug,
        ]);

        return [
            'redirect' => cp_route('product-categories.edit', [
                'category' => $category->uuid,
            ]),
        ];
    }

    public function show(ProductCategory $category)
    {
        $this->authorize('view', $category);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')]]);

        return view('simple-commerce::cp.product-categories.show', [
            'crumbs'    => $crumbs,
            'products'  => Product::where('product_category_id', $category->id)->paginate(config('statamic.cp.pagination_size')),
            'category'  => $category,
            'createUrl' => (new Product())->createUrl(),
        ]);
    }

    public function edit(ProductCategory $category)
    {
        $this->authorize('edit', $category);

        $crumbs = Breadcrumbs::make([['text' => 'Simple Commerce'], ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')]]);

        $blueprint = (new ProductCategory())->blueprint();
        $fields = $blueprint->fields()->addValues($category->toArray())->preProcess();

        return view('simple-commerce::cp.product-categories.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
            'action'    => cp_route('product-categories.update', ['category' => $category->uuid]),
        ]);
    }

    public function update(ProductCategoryRequest $request, ProductCategory $category): ProductCategory
    {
        $this->authorize('update', $category);

        $category->update([
            'title' => $request->title,
            'slug' => $request->slug,
        ]);

        return $category->refresh();
    }

    public function destroy(ProductCategory $category)
    {
        $this->authorize('delete', $category);

        // TODO: decide what we should do with products in this category

        $category->delete();
    }
}
