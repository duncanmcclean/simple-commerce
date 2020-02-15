<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class CartShipping extends Model
{
    use HasUuid;

    protected $table = 'cart_shipping';

    protected $fillable = [
        'uuid', 'shipping_zone_id', 'cart_id',
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
