<?php

namespace DuncanMcClean\SimpleCommerce;

use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Statamic\Facades\Site;

class DebugbarDataCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
{
    use CartDriver;

    public function collect()
    {
        if (! $this->hasCart()) {
            return [
                'Cart ID' => 'No cart found.',
            ];
        }

        $cart = $this->getCart();

        return [
            'Cart ID' => $cart->id,
            'Line Items' => $cart->lineItems()->map(function ($lineItem) {
                $product = $lineItem->product();

                $formattedTaxAmount = Currency::parse($lineItem->tax()['amount'], Site::current());
                $formattedItemAmount = Currency::parse($lineItem->total(), Site::current());

                return "{$lineItem->quantity()} X {$product->get('title')} (Tax: {$formattedTaxAmount}, Total: {$formattedItemAmount})";
            })->join(', '),
            'Items Total' => Currency::parse($cart->itemsTotal(), Site::current()),
            'Tax Total' => Currency::parse($cart->taxTotal(), Site::current()),
            'Shipping Total' => Currency::parse($cart->shippingTotal(), Site::current()),
            'Coupon Total' => Currency::parse($cart->couponTotal(), Site::current()),
            'Grand Total' => Currency::parse($cart->grandTotal(), Site::current()),
            'Site Currency' => Currency::get(Site::current())['symbol'].' '.Currency::get(Site::current())['name'],
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
