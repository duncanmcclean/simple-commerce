<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class CartShipping extends Model
{
    protected $table = 'cart_shipping';

    protected $fillable = [
        'uid', 'shipping_zone_id', 'cart_id'
    ];

    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
