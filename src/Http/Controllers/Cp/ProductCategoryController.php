<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Http\Requests\ProductCategoryStoreRequest;
use Damcclean\Commerce\Http\Requests\ProductCategoryUpdateRequest;
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
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
        ]);

        $categories = ProductCategory::all()
            ->map(function ($category) {
                return array_merge($category->toArray(), [
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
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
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
        $validation = $request->validated();

        $category = new ProductCategory();
        $category->uid = (new Stache())->generateId();
        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->save();

        return ['redirect' => cp_route('product-categories.edit', ['category' => $category->uid])];
    }

    public function edit(ProductCategory $category)
    {
        $crumbs = Breadcrumbs::make([
            ['text' => 'Commerce', 'url' => cp_route('commerce.dashboard')],
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
        $validated = $request->validated();

        $category->title = $request->title;
        $category->slug = $request->slug;
        $category->save();

        return $category;
    }

    public function destroy(ProductCategory $category)
    {
        $category->delete();

        return redirect(cp_route('product-categories.index'));
    }
}
