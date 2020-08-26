<?php

namespace DoubleThreeDigital\SimpleCommerce\Actions;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Exception;
use Statamic\Actions\Action;
use Statamic\Entries\Entry;

class RefundAction extends Action
{
    public static function title()
    {
        return 'Refund';
    }

    public function visibleTo($item)
    {
        return $item instanceof Entry &&
            $item->collectionHandle() === config('simple-commerce.collections.orders') &&
            ($item->data()->has('is_paid') && $item->data()->get('is_paid')) &&
            ($item->data()->get('is_refunded') === false || $item->data()->get('is_refunded') === null);
    }

    public function visibleToBulk($items)
    {
        return false;
    }

    public function run($items, $values)
    {
        collect($items)
            ->each(function ($entry) {
                $cart = Cart::find($entry->id());

                $gateway = new $cart->data['gateway']();

                $refund = $gateway->refundCharge($cart->data['gateway_data']);

                $cart->update([
                    'is_refunded' => true,
                    'gateway_data' => array_merge($cart->data['gateway_data'], [
                        'refund' => $refund,
                    ]),
                    'order_status' => 'refunded',
                ]);
            });
    }
}
