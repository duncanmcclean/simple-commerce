<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

class SiteNotConfiguredException extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create('You may have forgotten to add the site to the Simple Commerce config file.')
            ->setSolutionDescription('You need to add the site so Simple Commerce knows which currency and shipping methods to use. Follow the steps in the documentation to add a site.')
            ->setDocumentationLinks([
                'Multi-site' => 'https://simple-commerce.duncanmcclean.com/multisite',
            ]);
    }
}
