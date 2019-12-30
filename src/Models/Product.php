<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'slug' => 'product_category_id', 'uid',
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
}
