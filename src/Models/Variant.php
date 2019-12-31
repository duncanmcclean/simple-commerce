<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'sku', 'price', 'stock', 'unlimited_stock', 'max_quantity', 'product_id', 'uid', 'description', 'variant_attributes',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function setVariantAttributesAttribute($value)
    {
        $this->attributes['variant_attributes'] = json_encode($value);
    }

    public function getVariantAttributesAttribute($value)
    {
        return json_decode($value);
    }
}
