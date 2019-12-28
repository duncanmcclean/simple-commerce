<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'default_billing_address_id', 'default_shipping_address_id',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function defaultBillingAddress()
    {
        return $this->hasOne(Address::class);
    }

    public function defaultShippingAddress()
    {
        return $this->hasOne(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
