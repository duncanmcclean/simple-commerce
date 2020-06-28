<?php

namespace DoubleThreeDigital\SimpleCommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Entry;
use Sushi\Sushi;

class Order extends Model
{
    use Sushi;

    public function getRows()
    {
        return Entry::whereCollection('orders');
    }

    public function scopeByCart($query)
    {
        $query->where('status', 'cart');
    }

    public function scopeByOrder($query)
    {
        $query->where('status', 'order');
    }
}
