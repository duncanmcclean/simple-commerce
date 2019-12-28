<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'sku', 'price', 'stock', 'unlimited_stock', 'max_quantity', 'product_id',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
