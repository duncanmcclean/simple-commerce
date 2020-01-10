<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'title', 'slug', 'uid', 'category_route', 'product_route',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function createUrl()
    {
        return cp_route('product-categories.create');
    }

    public function showUrl()
    {
        return cp_route('product-categories.show', ['category' => $this->uid]);
    }

    public function editUrl()
    {
        return cp_route('product-categories.edit', ['category' => $this->uid]);
    }

    public function updateUrl()
    {
        return cp_route('product-categories.update', ['category' => $this->uid]);
    }

    public function deleteUrl()
    {
        return cp_route('product-categories.destroy', ['category' => $this->uid]);
    }
}
