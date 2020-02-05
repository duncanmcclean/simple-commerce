<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency as CurrencyHelper;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'sku', 'price', 'stock', 'unlimited_stock', 'max_quantity', 'product_id', 'uuid', 'description', 'variant_attributes', 'name',
    ];

    protected $appends = [
        'outOfStock',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
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
        return json_decode($value, true);
    }

    public function getOutOfStockAttribute()
    {
        if ($this->unlimited_stock) {
            return false;
        }

        if ($this->stock <= 0) {
            return true;
        }

        return false;
    }
}
