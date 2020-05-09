<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class TaxRate extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'description', 'rate',
    ];

    public function lineItems()
    {
        return $this->hasMany(LineItem::class);
    }

    public function products()
    {
        return $this->hasMany(self::class);
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
