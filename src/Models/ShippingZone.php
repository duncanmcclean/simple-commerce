<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'country_id', 'state_id', 'start_of_zip_code', 'rate', 'uuid',
    ];

    protected $appends = [
        'updateUrl', 'deleteUrl',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function carts()
    {
        return $this->hasMany(CartShipping::class);
    }

    public function getUpdateUrlAttribute()
    {
        return cp_route('commerce-api.shipping-zones.update', ['zone' => $this->attributes['uuid']]);
    }

    public function getDeleteUrlAttribute()
    {
        return cp_route('commerce-api.shipping-zones.destroy', ['zone' => $this->attributes['uuid']]);
    }
}
