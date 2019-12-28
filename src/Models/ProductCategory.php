<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $fillable = [
        'title', 'slug',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
