<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name', 'iso', 'uuid',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function states()
    {
        return $this->hasMany(State::class);
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
