<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasUuid;

    protected $guarded = [];

    protected $dispatchesEvents = [
        'updated' => AttributeUpdated::class,
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function attributable()
    {
        return $this->morphTo();
    }
}
