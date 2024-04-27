<?php

namespace DuncanMcClean\SimpleCommerce\Listeners;

use DuncanMcClean\SimpleCommerce\Contracts\Order;
use DuncanMcClean\SimpleCommerce\Orders\Cart\Drivers\CartDriver;
use DuncanMcClean\SimpleCommerce\SimpleCommerce;
use Illuminate\Auth\Events\Logout;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;

class RemoveCustomerFromOrder
{
    use CartDriver;

    public function handle(Logout $event)
    {
        if (! $this->isOrExtendsClass(SimpleCommerce::customerDriver()['repository'], \DuncanMcClean\SimpleCommerce\Customers\UserCustomerRepository::class)) {
            return;
        }

        if ($this->hasCart()) {
            $this->getCart()->customer(null)->save();
        }
    }

    protected function isOrExtendsClass(string $class, string $classToCheckAgainst): bool
    {
        return is_subclass_of($class, $classToCheckAgainst)
            || $class === $classToCheckAgainst;
    }
}
