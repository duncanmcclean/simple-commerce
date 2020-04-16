<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'type', 'minimum', 'maximum', 'rate', 'shipping_rate_id',
    ];

    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }
}
