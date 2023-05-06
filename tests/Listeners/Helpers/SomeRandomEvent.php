<?php

namespace DoubleThreeDigital\SimpleCommerce\Tests\Listeners\Helpers;

use DoubleThreeDigital\SimpleCommerce\Contracts\Order as OrderContract;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SomeRandomEvent
{
    public function __construct(public OrderContract $order)
    {
    }
}
