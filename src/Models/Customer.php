<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Customer extends Model
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'uuid',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
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
        return cp_route('customers.edit', ['customer' => $this->uuid]);
    }

    public function updateUrl()
    {
        return cp_route('customers.update', ['customer' => $this->uuid]);
    }

    public function deleteUrl()
    {
        return cp_route('customers.destroy', ['customer' => $this->uuid]);
    }
}
