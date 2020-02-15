<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'abbreviation', 'country_id',
    ];

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
