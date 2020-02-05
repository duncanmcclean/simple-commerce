<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'iso', 'uuid', 'symbol', 'name',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
