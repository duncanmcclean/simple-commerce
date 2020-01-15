<?php

namespace Damcclean\Commerce\Notifications;

use Damcclean\Commerce\Models\Product;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;

class OrderSuccessful extends Notification
{
    public $order;
    public $customer;

    public function __construct($order, $customer)
    {
        $this->order = $order;
        $this->customer = $customer;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        // TODO: work on getting this stuff sorted, after we have proper item to cart stuff sorted :)

//        $products = collect($this->order->data->items)
//            ->map(function ($orderProduct) {
//                $product = Product::find($orderProduct['id']);
//
//                $amount = Money::{strtoupper(config('commerce.currency.code'))}($product['price'] * 100);
//                $moneyFormatter = new DecimalMoneyFormatter(new ISOCurrencies());
//
//                return [
//                    'title' => $product['title'],
//                    'quantity' => $orderProduct['quantity'],
//                    'price' => $moneyFormatter->format($amount),
//                ];
//            });

//        return (new MailMessage())
//            ->success()
//            ->subject('Order successful')
//            ->markdown('commerce::mail.order-successful', [
//                'order' => $this->order->data,
//                'customer' => $this->customer,
//                'products' => $products->toArray(),
//            ]);
    }
}
