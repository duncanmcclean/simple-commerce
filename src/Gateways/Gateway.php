<?php

namespace DoubleThreeDigital\SimpleCommerce\Gateways;

interface Gateway
{
    public function capture();

    public function authorize($paymentMethod);

    // validation rules for submitting to the checkout form
    public function rules();

    // returns html and js required to use the gateway
    public function paymentForm();

    public function refund($payment);

    public function name(): string;
}
