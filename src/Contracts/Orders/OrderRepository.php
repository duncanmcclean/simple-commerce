<?php

namespace DuncanMcClean\SimpleCommerce\Contracts\Orders;

use DuncanMcClean\SimpleCommerce\Contracts\Cart\Cart;

interface OrderRepository
{
    public function all();

    public function query();

    public function find($id): ?Order;

    public function findOrFail($id): Order;

    public function make(): Order;

    public function makeFromCart(Cart $cart): Order;

    public function save(Order $order): void;

    public function delete(Order $order): void;

    public static function bindings(): array;
}
