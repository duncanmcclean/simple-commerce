<?php

namespace DuncanMcClean\SimpleCommerce\Cart\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LineItemModel extends Model
{
    use HasUuids;

    protected $guarded = [];

    public function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'sub_total' => 'integer',
            'tax_total' => 'integer',
            'total' => 'integer',
            'data' => 'json',
        ];
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('statamic.simple-commerce.carts.line_items_table', 'cart_line_items'));
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(app('simple-commerce.carts.eloquent.model'), ownerKey: 'cart_id');
    }
}
