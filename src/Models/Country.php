<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name', 'iso',
    ];

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function states()
    {
        return $this->hasMany(State::class);
    }
}
