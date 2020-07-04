<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags\Concerns;

trait CheckoutTags
{
    public function checkout()
    {
        return $this->createForm(
            route('statamic.simple-commerce.checkout.store'),
            [],
            'POST'
        );
    }
}