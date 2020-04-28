<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Blueprint;

class Coupon extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'name', 'code', 'type', 'value', 'minimum_total', 'total_uses', 'start_date', 'end_date',
    ];

    public function blueprint()
    {
        return Blueprint::find('coupon');
    }
}