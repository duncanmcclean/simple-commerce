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

    public function updateUrl()
    {
        return cp_route('tax-rates.update', ['rate' => $this->attributes['uuid']]);
    }

    public function deleteUrl()
    {
        return cp_route('tax-rates.destroy', ['rate' => $this->attributes['uuid']]);
    }

    public function blueprint()
    {
        return Blueprint::find('tax_rate');
    }
}
