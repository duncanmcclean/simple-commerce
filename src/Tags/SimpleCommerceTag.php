<?php

namespace DoubleThreeDigital\SimpleCommerce\Tags;

use DoubleThreeDigital\SimpleCommerce\Models\Country;
use Statamic\Tags\Tags;

class SimpleCommerceTag extends Tags
{
    use Concerns\FormBuilder, 
        Concerns\CartTags, 
        Concerns\CheckoutTags, 
        Concerns\CouponTags, 
        Concerns\CustomerTags, 
        Concerns\GatewayTags, 
        Concerns\ShippingTags;

    protected static $handle = 'sc';
    protected static $aliases = ['simple-commerce'];

    public function countries()
    {
        return Country::all()->toArray();
    }
}