<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'name', 'abbreviation', 'country_id', 'uid',
    ];

    public function getRouteKeyName()
    {
        return 'uid';
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
