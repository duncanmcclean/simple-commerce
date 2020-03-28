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
        'updateUrl', 'deleteUrl', 'formatted_price',
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

    public function getUpdateUrlAttribute()
    {
        // TODO: this should not be an attribute, it should be a method
        return cp_route('commerce-api.shipping-zones.update', ['zone' => $this->attributes['uuid']]);
    }

    public function getDeleteUrlAttribute()
    {
        // TODO: this should not be an attribute, it should be a method
        return cp_route('commerce-api.shipping-zones.destroy', ['zone' => $this->attributes['uuid']]);
    }

    public function getFormattedPriceAttribute()
    {
        return (new \DoubleThreeDigital\SimpleCommerce\Helpers\Currency())->parse($this->attributes['price']);
    }

    public function blueprint()
    {
        return Blueprint::find('shipping_zone');
    }
}
