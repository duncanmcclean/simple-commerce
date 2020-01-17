<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug' => 'product_category_id', 'uid', 'is_enabled', 'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected $appends = [
        'from_price', 'to_price',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function getFromPriceAttribute()
    {
        return Variant::where('product_id', $this->attributes['id'])->get()->sortByDesc('price')->first()->price;
    }

    public function getToPriceAttribute()
    {
        return Variant::where('product_id', $this->attributes['id'])->get()->sortBy('price')->first()->price;
    }

    public function createUrl()
    {
        return cp_route('products.create');
    }

    public function editUrl()
    {
        return cp_route('products.edit', ['product' => $this->uid]);
    }

    public function updateUrl()
    {
        return cp_route('products.update', ['product' => $this->uid]);
    }

    public function deleteUrl()
    {
        return cp_route('products.destroy', ['product' => $this->uid]);
    }
}
