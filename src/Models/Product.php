<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasAttributes;

    protected $fillable = [
        'title', 'slug' => 'product_category_id', 'uuid', 'is_enabled', 'description',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
    ];

    protected $appends = [
        'from_price', 'to_price', 'url', 'variant_count',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
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

    public function getUrlAttribute()
    {
        return route('products.show', ['product' => $this->attributes['slug']]);
    }

    public function getVariantCountAttribute()
    {
        return sprintf('%s %s', $count = $this->variants->count(), Str::plural('variant', $count));
    }

    public function createUrl()
    {
        return cp_route('products.create');
    }

    public function editUrl()
    {
        return cp_route('products.edit', ['product' => $this->uuid]);
    }

    public function updateUrl()
    {
        return cp_route('products.update', ['product' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('products.destroy', ['product' => $this->uuid]);
    }
}
