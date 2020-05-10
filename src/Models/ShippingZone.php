<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class ShippingZone extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name',
    ];

    protected $appends = [
        'listOfCountries',
    ];

    public function rates()
    {
        return $this->hasMany(ShippingRate::class);
    }

    public function countries()
    {
        return $this->hasMany(Country::class);
    }

    public function getListOfCountriesAttribute()
    {
        $countries = [];

        $this->countries()
            ->select('name')
            ->get()
            ->each(function ($country) use (&$countries) {
                $countries[] = $country->name;
            });

        return implode(', ', $countries);
    }

    public function editUrl()
    {
        return cp_route('shipping-zones.edit', ['zone' => $this->attributes['uuid']]);
    }

    public function updateUrl()
    {
        return cp_route('shipping-zones.update', ['zone' => $this->attributes['uuid']]);
    }

    public function deleteUrl()
    {
        return cp_route('shipping-zones.destroy', ['zone' => $this->attributes['uuid']]);
    }

    public function blueprint()
    {
        return Blueprint::setDirectory(__DIR__.'/../../resources/blueprints')
            ->find('shipping_zone');
    }
}
