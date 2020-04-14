<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'order_id', 'variant_id', 'tax_category_id', 'shipping_category_id', 'description', 'sku', 'price', 'weight', 'height', 'length', 'width', 'total', 'quantity', 'note',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->hasOne(Variant::class);
    }

    public function taxCategory()
    {
        //
    }

    public function shippingCategory()
    {
        //
    }
}
