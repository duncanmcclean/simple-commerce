<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'title', 'slug', 'uuid',
    ];

    protected $appends = [
        'url',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getUrlAttribute()
    {
        return route('categories.show', ['category' => $this->attributes['slug']]);
    }

    public function createUrl()
    {
        return cp_route('product-categories.create');
    }

    public function showUrl()
    {
        return cp_route('product-categories.show', ['category' => $this->uuid]);
    }

    public function editUrl()
    {
        return cp_route('product-categories.edit', ['category' => $this->uuid]);
    }

    public function updateUrl()
    {
        return cp_route('product-categories.update', ['category' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('product-categories.destroy', ['category' => $this->uuid]);
    }
}
