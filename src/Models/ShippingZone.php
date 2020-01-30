<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingZone extends Model
{
    protected $fillable = [
        'country_id', 'state_id', 'start_of_zip_code', 'rate', 'uid',
    ];

    protected $appends = [
        'updateUrl', 'deleteUrl',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function getUpdateUrlAttribute()
    {
        return cp_route('commerce-api.shipping-zones.update', ['zone' => $this->attributes['uid']]);
    }

    public function getDeleteUrlAttribute()
    {
        return cp_route('commerce-api.shipping-zones.destroy', ['zone' => $this->attributes['uid']]);
    }
}
