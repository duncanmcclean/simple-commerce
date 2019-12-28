<?php

namespace Damcclean\Commerce\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'iso', 'primary',
    ];

    protected $casts = [
        'primary' => 'boolean',
    ];

    public function orders()
    {
        return $this->hasMany(Currency::class);
    }
}
