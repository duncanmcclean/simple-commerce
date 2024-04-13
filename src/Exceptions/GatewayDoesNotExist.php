<?php

namespace DuncanMcClean\SimpleCommerce\Exceptions;

use Exception;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;

class GatewayDoesNotExist extends Exception implements ProvidesSolution
{
    protected $gateway;

    public function __construct(string $gateway)
    {
        $this->gateway = $gateway;

        parent::__construct("Gateway [{$this->gateway}] does not exist");
    }

    public function getSolution(): Solution
    {
        return BaseSolution::create("The gateway [{$this->gateway}] does not exist")
            ->setSolutionDescription("Ensure you've configured the gateway correctly in `config/simple-commerce.php`.")
            ->setDocumentationLinks([
                'Configuring Gateways' => 'https://simple-commerce.duncanmcclean.com/gateways#configuration',
            ]);
    }
}
