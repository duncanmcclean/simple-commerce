<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Http\Requests\ProductCategoryStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductCategoryUpdateRequest;
use Damcclean\Commerce\Models\Product;
use Damcclean\Commerce\Models\ProductCategory;
use Statamic\CP\Breadcrumbs;
use Statamic\Facades\Blueprint;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Stache\Stache;

class ProductCategoryController extends CpController
{
    public function index()
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
        ]);

        $categories = ProductCategory::all()
            ->map(function ($category) {
                return array_merge($category->toArray(), [
                    'view_url' => cp_route('product-categories.show', ['category' => $category->uid]),
                    'edit_url' => cp_route('product-categories.edit', ['category' => $category->uid]),
                    'delete_url' => cp_route('product-categories.destroy', ['category' => $category->uid]),
                ]);
            });

        return view('commerce::cp.product-categories.index', [
            'crumbs' => $crumbs,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('create', ProductCategory::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')],
        ]);

        $blueprint = Blueprint::find('product_category');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.product-categories.create', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $fields->values(),
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function store(ProductCategoryStoreRequest $request)
    {
        $this->authorize('create', ProductCategory::class);

        $validation = $request->validated();

        $category = new ProductCategory();
        $category->uid = (new Stache())->generateId();
        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->save();

        return ['redirect' => cp_route('product-categories.edit', ['category' => $category->uid])];
    }

    public function show(ProductCategory $category)
    {
        $this->authorize('view', ProductCategory::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')],
        ]);

        $products = Product::all()
            ->where('product_category_id', $category->id)
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

    public function edit(ProductCategory $category)
    {
        $this->authorize('edit', ProductCategory::class);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')],
        ]);

        $blueprint = Blueprint::find('product_category');

        $fields = $blueprint->fields();
        $fields = $fields->addValues([]);
        $fields = $fields->preProcess();

        return view('commerce::cp.product-categories.edit', [
            'blueprint' => $blueprint->toPublishArray(),
            'values'    => $category,
            'meta'      => $fields->meta(),
            'crumbs'    => $crumbs,
        ]);
    }

    public function update(ProductCategoryUpdateRequest $request, ProductCategory $category)
    {
        $this->authorize('update', ProductCategory::class);

        $validated = $request->validated();

        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->save();

        return $category;
    }

    public function destroy(ProductCategory $category)
    {
        $this->authorize('delete', ProductCategory::class);

        $category->delete();

        return redirect(cp_route('product-categories.index'));
    }
}
