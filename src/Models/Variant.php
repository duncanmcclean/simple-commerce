<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Helpers\Currency as CurrencyHelper;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasAttributes;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    use HasAttributes, HasUuid;

    protected $fillable = [
        'sku', 'price', 'stock', 'unlimited_stock', 'max_quantity', 'product_id', 'uuid', 'description', 'name',
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
