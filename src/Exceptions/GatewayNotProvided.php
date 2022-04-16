<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

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
