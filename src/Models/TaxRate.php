<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class TaxRate extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'country_id', 'state_id', 'start_of_zip_code', 'rate', 'name',
    ];

    protected $appends = [
        'updateUrl', 'deleteUrl',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function cartTax()
    {
        return $this->hasMany(CartTax::class);
    }

    public function getUpdateUrlAttribute()
    {
        // TODO: this should not be an attribute, it should be a method
        return cp_route('commerce-api.tax-rates.update', ['rate' => $this->attributes['uuid']]);
    }

    public function getDeleteUrlAttribute()
    {
        // TODO: this should not be an attribute, it should be a method
        return cp_route('commerce-api.tax-rates.destroy', ['rate' => $this->attributes['uuid']]);
    }

    public function blueprint()
    {
        return Blueprint::find('tax_rate');
    }
}
