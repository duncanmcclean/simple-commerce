<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'name', 'abbreviation', 'country_id', 'uuid',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function taxRates()
    {
        return $this->hasMany(TaxRate::class);
    }

    public function shippingZones()
    {
        return $this->hasMany(ShippingZone::class);
    }
}
