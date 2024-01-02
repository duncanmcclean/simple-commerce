<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Fixtures\Events;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;

class AnotherRandomEvent
{
    public function __construct(public OrderContract $order, public string $someOtherProperty, public bool $somethingElseThatIsAProperty)
    {
    }
}
