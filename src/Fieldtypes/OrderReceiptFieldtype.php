<?php

namespace DuncanMcClean\SimpleCommerce\Fieldtypes;

use DuncanMcClean\SimpleCommerce\Orders\LineItem;
use DuncanMcClean\SimpleCommerce\Orders\LineItems;
use DuncanMcClean\SimpleCommerce\Support\Money;
use Statamic\Facades\Site;
use Statamic\Fields\Fieldtype;

class OrderReceiptFieldtype extends Fieldtype
{
    protected $selectable = false;

    public function preload()
    {
        return LineItems::blueprint()->fields()->meta();
    }

    public function preProcess($data)
    {
        $order = $this->field->parent();

        return [
            'line_items' => $order->lineItems()->map(fn (LineItem $lineItem) => [
                'product' => $lineItem->product() ? [
                    'id' => $lineItem->product()->id(),
                    'reference' => $lineItem->product()->reference(),
                    'title' => $lineItem->product()->value('title'),
                    'edit_url' => $lineItem->product()->editUrl(),
                ] : ['id' => $lineItem->product, 'title' => $lineItem->product, 'invalid' => true],
                'variant' => $lineItem->variant() ? [
                    'key' => $lineItem->variant()->key(),
                    'name' => $lineItem->variant()->name(),
                ] : null,
                'unit_price' => Money::format($lineItem->unitPrice(), Site::selected()),
                'quantity' => $lineItem->quantity(),
                'sub_total' => Money::format($lineItem->subTotal(), Site::selected()),
                'total' => Money::format($lineItem->total(), Site::selected()),
            ])->all(),
            'coupon' => $order->coupon() ? [
                'code' => $order->coupon()->code(),
                'discount' => $order->coupon()->discountText(),
            ] : null,
            'shipping' => $order->shippingOption() ? [
                'name' => $order->shippingOption()->name(),
                'price' => Money::format($order->shippingOption()->price(), Site::selected()),
            ] : null,
            'taxes' => ! config('statamic.simple-commerce.taxes.price_includes_tax') ? [
                'breakdown' => collect($order->taxBreakdown())->map(fn ($tax) => [
                    'rate' => $tax['rate'],
                    'description' => $tax['description'],
                    'amount' => Money::format($tax['amount'], Site::selected()),
                ])->all(),
            ] : null,
            'refund' => [
                'issued' => $order->get('amount_refunded', 0) > 0,
            ],
            'totals' => [
                'sub_total' => Money::format($order->subTotal(), Site::selected()),
                'discount_total' => Money::format($order->discountTotal(), Site::selected()),
                'shipping_total' => Money::format($order->shippingTotal(), Site::selected()),
                'tax_total' => Money::format($order->taxTotal(), Site::selected()),
                'grand_total' => Money::format($order->grandTotal(), Site::selected()),
                'amount_refunded' => Money::format($order->get('amount_refunded'), Site::selected()),
            ],
        ];
    }

    public function process($data): null
    {
        return null;
    }
}
