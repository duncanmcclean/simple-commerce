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

        $categories = ProductCategory::all();

        return view('commerce::cp.product-categories.index', [
            'crumbs' => $crumbs,
            'categories' => $categories,
            'createUrl' => (new ProductCategory())->createUrl(),
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
        $category->category_route = $request->category_route;
        $category->product_route = $request->product_route;
        $category->save();

        return ['redirect' => cp_route('product-categories.edit', ['category' => $category->uid])];
    }

    public function show(ProductCategory $category)
    {
        $this->authorize('view', $category);

        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => '#'],
            ['text' => 'Product Categories', 'url' => cp_route('product-categories.index')],
        ]);

        $products = Product::all()
            ->where('product_category_id', $category->id);

        return view('commerce::cp.products.index', [
            'crumbs' => $crumbs,
            'products' => $products,
            'createUrl' => (new Product())->createUrl(),
        ]);
    }

    public function edit(ProductCategory $category)
    {
        $this->authorize('edit', $category);

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
        $this->authorize('update', $category);

        $validated = $request->validated();

        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->category_route = $request->category_route;
        $category->product_route = $request->product_route;
        $category->save();

        return $category;
    }

    public function destroy(ProductCategory $category)
    {
        $this->authorize('delete', $category);

        $category->delete();

        return redirect(cp_route('product-categories.index'));
    }
}
