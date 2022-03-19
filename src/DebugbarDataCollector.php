<?php

namespace DoubleThreeDigital\SimpleCommerce;

use DoubleThreeDigital\SimpleCommerce\Facades\Currency;
use DoubleThreeDigital\SimpleCommerce\Facades\Product;
use DoubleThreeDigital\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use Statamic\Facades\Site;

class DebugbarDataCollector extends \DebugBar\DataCollector\DataCollector implements \DebugBar\DataCollector\Renderable
{
    use CartDriver;

    public function collect()
    {
        $cart = $this->getCart();

        return [
            'Cart ID' => $cart->id,
            'Line Items' => $cart->lineItems()->map(function ($lineItem) {
                $product = Product::find($lineItem['product']);

                $formattedTaxAmount = Currency::parse($lineItem['tax']['amount'], Site::current());
                $formattedItemAmount = Currency::parse($lineItem['total'], Site::current());

                return "{$lineItem['quantity']} X {$product->get('title')} (Tax: {$formattedTaxAmount}, Total: {$formattedItemAmount})";
            })->join(', '),
            'Items Total' => Currency::parse($cart->get('items_total'), Site::current()),
            'Tax Total' => Currency::parse($cart->get('tax_total'), Site::current()),
            'Shipping Total' => Currency::parse($cart->get('shipping_total'), Site::current()),
            'Coupon Total' => Currency::parse($cart->get('coupon_total'), Site::current()),
            'Grand Total' => Currency::parse($cart->get('grand_total'), Site::current()),
            'Site Currency' => Currency::get(Site::current())['symbol'] . ' ' . Currency::get(Site::current())['name'],
            'Enabled Gateways' => collect(SimpleCommerce::gateways())->pluck('name')->join(', '),
        ];
    }

    public function getName()
    {
        return 'simple-commerce';
    }

    public function getWidgets()
    {
        return [
            'simple-commerce' => [
                'icon' => 'shopping-cart',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'simple-commerce',
                'default' => '{}',
            ],
        ];
    }
}
