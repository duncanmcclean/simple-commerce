<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'iso', 'primary', 'uid', 'symbol', 'name',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function orders()
    {
        return $this->hasMany(Currency::class);
    }
}
