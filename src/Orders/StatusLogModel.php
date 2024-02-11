<?php

namespace DuncanMcClean\SimpleCommerce\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusLogModel extends Model
{
    use HasFactory;

    protected $table = 'status_log';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'order_id' => 'integer',
        'timestamp' => 'datetime',
        'data' => 'json',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
