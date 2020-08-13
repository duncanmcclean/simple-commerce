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
            ($item->data()->has('is_paid') && $item->data()->get('is_paid'));

            // also might want to check if order has been refunded
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

                if (! isset($cart['data']['gateway'])) {
                    // might want to create sc exception and localize text
                    throw new Exception('This order does not have an attached gateway.');
                }

                $gateway = new $cart->data['gateway']();

                $refund = $gateway->refundCharge($cart->data['gateway_data']);

                $cart->update([
                    'is_refunded' => true,
                    'gateway_data' => array_merge($cart->data['gateway_data'], [
                        'refund' => $refund,
                    ]),
                ]);
            });
    }
}
