<?php

namespace DuncanMcClean\SimpleCommerce;

use DuncanMcClean\SimpleCommerce\Facades\Cart;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Facades\Site;

class DebugbarDataCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
{
    public function collect()
    {
        if (! Cart::exists()) {
            return [
                'Cart ID' => 'No cart found.',
            ];
        }

        $cart = Cart::get();

        return [
            'Cart ID' => $cart->id,
            'Line Items' => $cart->lineItems()->map(function ($lineItem) {
                $product = $lineItem->product();

                $formattedTaxAmount = Money::format($lineItem->tax()['amount'], Site::current());
                $formattedItemAmount = Money::format($lineItem->total(), Site::current());

                return "{$lineItem->quantity()} X {$product->get('title')} (Tax: {$formattedTaxAmount}, Total: {$formattedItemAmount})";
            })->join(', '),
            'Items Total' => Money::format($cart->itemsTotal(), Site::current()),
            'Tax Total' => Money::format($cart->taxTotal(), Site::current()),
            'Shipping Total' => Money::format($cart->shippingTotal(), Site::current()),
            'Coupon Total' => Money::format($cart->couponTotal(), Site::current()),
            'Grand Total' => Money::format($cart->grandTotal(), Site::current()),
            'Site Money' => Money::get(Site::current())['symbol'].' '.Money::get(Site::current())['name'],
            'Enabled Gateways' => SimpleCommerce::gateways()->pluck('name')->join(', '),
        ];
    }

    public function getName()
    {
        return 'Simple Commerce';
    }

    public function getWidgets()
    {
        return [
            'Simple Commerce' => [
                'icon' => 'shopping-cart',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'Simple Commerce',
                'default' => '{}',
            ],
        ];
    }
}
