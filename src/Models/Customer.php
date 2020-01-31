<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'uid',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function createUrl()
    {
        return cp_route('customers.create');
    }

    public function editUrl()
    {
        return cp_route('customers.edit', ['customer' => $this->uid]);
    }

    public function updateUrl()
    {
        return cp_route('customers.update', ['customer' => $this->uid]);
    }

    public function deleteUrl()
    {
        return cp_route('customers.destroy', ['customer' => $this->uid]);
    }
}
