<?php

namespace DoubleThreeDigital\SimpleCommerce\Mail\BackOffice;

use DoubleThreeDigital\SimpleCommerce\Facades\Cart;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class OrderPaid extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $orderId;

    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    public function build()
    {
        $order = Cart::find($this->orderId);

        return $this->markdown('simple-commerce::back-office.order-paid')
            ->subject('Order Paid') // todo: add translation
            ->to(Config::get('simple-commerce.notifications.back_office.to'))
            ->with('order', $order->entry()->toAugmentedArray());
    }
}
