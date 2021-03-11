<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

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
                'Configuring Gateways' => 'https://sc-docs.doublethree.digital/v2.2/gateways#configuration',
            ]);
    }
}
