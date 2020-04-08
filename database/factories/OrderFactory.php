<?php

use App\User;
use DoubleThreeDigital\SimpleCommerce\Models\Address;
use DoubleThreeDigital\SimpleCommerce\Models\Currency;
use DoubleThreeDigital\SimpleCommerce\Models\OrderStatus;
use Faker\Generator as Faker;
use DoubleThreeDigital\SimpleCommerce\Models\Order;
use Statamic\Stache\Stache;
use DoubleThreeDigital\SimpleCommerce\Models\CartItem;
use DoubleThreeDigital\SimpleCommerce\Models\Cart;
use DoubleThreeDigital\SimpleCommerce\Models\CartShipping;
use DoubleThreeDigital\SimpleCommerce\Models\CartTax;
use DoubleThreeDigital\SimpleCommerce\Helpers\Cart as CartHelper;
use Illuminate\Support\Facades\Config;

$factory->define(Order::class, function (Faker $faker) {
    $currency = factory(Currency::class)->create();
    Config::set('simple-commerce.currency.iso', $currency->iso);

    $cart = factory(Cart::class)->create();
    $items = factory(CartItem::class, 5)->create(['cart_id' => $cart->id]);
    $shipping = factory(CartShipping::class)->create(['cart_id' => $cart->id]);
    $tax = factory(CartTax::class)->create(['cart_id' => $cart->id]);

    return [
        'uuid' => (new Stache())->generateId(),
        'billing_address_id' => function () {
            return factory(Address::class)->create()->id;
        },
        'shipping_address_id' => function () {
            return factory(Address::class)->create()->id;
        },
        'customer_id' => function () {
            return factory(User::class)->create()->id;
        },
        'order_status_id' => function () {
            return factory(OrderStatus::class)->create()->id;
        },
        'items' => json_encode((new CartHelper())->orderItems($cart->uuid)),
        'total' => 0,
        'currency_id' => function () {
            return factory(Currency::class)->create()->id;
        },
        'gateway_data' => json_encode([]),
        'is_paid' => true,
        'is_refunded' => false,
    ];
});
