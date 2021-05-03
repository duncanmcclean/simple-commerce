<?php

namespace DoubleThreeDigital\SimpleCommerce\Exceptions;

use Exception;
use Facade\IgnitionContracts\BaseSolution;
use Facade\IgnitionContracts\ProvidesSolution;
use Facade\IgnitionContracts\Solution;

class CurrencyFormatterNotWorking extends Exception implements ProvidesSolution
{
    public function getSolution(): Solution
    {
        return BaseSolution::create("Simple Commerce requires this extension to preform currency formatting.")
            ->setSolutionDescription("Please install `php-intl` to use Simple Commerce.")
            ->setDocumentationLinks([
                'Simple Commerce Requirements' => 'https://sc-docs.doublethree.digital/v2.3/installation#requirements',
                'PHP-intl Documentation'       => 'https://www.php.net/manual/en/book.intl.php',
            ]);
    }
}
