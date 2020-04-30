<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use DoubleThreeDigital\SimpleCommerce\Events\AttributeUpdated;
use DoubleThreeDigital\SimpleCommerce\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    use HasUuid;

    protected $fillable = [
        'uuid', 'key', 'value',
    ];

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
