<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class ShippingZone extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'country_id', 'state_id', 'start_of_zip_code', 'price',
    ];

    protected $appends = [
        'formatted_price', 'name',
    ];

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

    public function updateUrl()
    {
        return cp_route('commerce-api.shipping-zones.update', ['zone' => $this->attributes['uuid']]);
    }

    public function deleteUrl()
    {
        return cp_route('commerce-api.shipping-zones.destroy', ['zone' => $this->attributes['uuid']]);
    }

    public function getFormattedPriceAttribute()
    {
        return (new \DoubleThreeDigital\SimpleCommerce\Helpers\Currency())->parse($this->attributes['price']);
    }

    public function getNameAttribute()
    {
        if ($this->state != null) {
            return $this->country->name.', '.$this->state->name.', '.$this->start_of_zip_code;
        }

        return $this->country->name.', '.$this->start_of_zip_code;
    }

    public function blueprint()
    {
        return Blueprint::find('shipping_zone');
    }
}
