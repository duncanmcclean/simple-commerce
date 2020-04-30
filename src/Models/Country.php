<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'iso', 'shipping_zone_id',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function shippingZone()
    {
        return $this->belongsTo(ShippingZone::class);
    }
}
