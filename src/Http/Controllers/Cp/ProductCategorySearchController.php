<?php

namespace Damcclean\Commerce\Http\Controllers\Cp;

use Damcclean\Commerce\Models\ProductCategory;
use Statamic\Http\Controllers\CP\CpController;

class ProductCategorySearchController extends CpController
{
    public function __invoke()
    {
        $query = request()->input('search');

        if (! $query) {
            $results = ProductCategory::all()
                ->map(function ($category) {
                    return array_merge($category->toArray(), [
                        'edit_url' => cp_route('product-categories.edit', ['category' => $category->id]),
                        'delete_url' => cp_route('product-categories.destroy', ['category' => $category->id]),
                    ]);
                });

            return $this->returnResponse($results);
        }

        $results = ProductCategory::all()
            ->filter(function ($item) use ($query) {
                return false !== stristr((string) $item['title'], $query);
            })
            ->map(function ($category) {
                return array_merge($category->toArray(), [
                    'edit_url' => cp_route('product-categories.edit', ['category' => $category->id]),
                    'delete_url' => cp_route('product-categories.destroy', ['category' => $category->id]),
                ]);
            });

        return $this->returnResponse($results);
    }

    public function returnResponse($results)
    {
        return response()->json([
            'data' => $results,
            'links' => [],
            'meta' => [
                'path' => cp_route('product-categories.search'),
                'sortColumn' => 'title',
            ],
        ]);
    }
}
