<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'address1', 'address2', 'address3', 'city', 'zip_code', 'state_id'. 'country_id', 'customer_id,'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function customer()
    {
        $model = config('simple-commerce.customers.model');
        return $this->belongsTo(new $model());
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
