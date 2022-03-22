<?php

namespace DoubleThreeDigital\SimpleCommerce\Fieldtypes\Variables;

use DoubleThreeDigital\SimpleCommerce\Facades\Order;

class ReceiptUrl extends VariableFieldtype
{
    protected static $handle = 'receipt_url';

    public static function title()
    {
        return 'SC: Receipt URL';
    }

    public function augment($value)
    {
        $order = Order::find($this->related()->id());

        return $order->receiptUrl();
    }
}
