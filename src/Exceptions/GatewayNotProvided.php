<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class GatewayNotProvided extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('You may have forgotten to provide the Gateway in the request.')
            ->setSolutionDescription('Ensure that `gateway` is provided in your request. It should be the class name of the gateway you wish to use')
            ->setDocumentationLinks([
                'Checkout Tag' => 'https://simple-commerce.duncanmcclean.com/tags/checkout-tag',
            ]);
    }
}
