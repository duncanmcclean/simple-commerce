<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Events\CustomerCreated;
use DoubleThreeDigital\SimpleCommerce\Events\CustomerUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Order;

trait IsACustomer
{
//    protected $dispatchesEvents = [
//        'created' => CustomerCreated::class,
//        'updated' => CustomerUpdated::class,
//    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
